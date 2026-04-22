<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Kitab;
use App\Models\KitabRevision;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class KitabPublicationService
{
    /**
     * Create draft kitab + initial revision.
     */
    public function createDraft(array $kitabData, int $actorId, ?string $note = null): Kitab
    {
        return DB::transaction(function () use ($kitabData, $actorId, $note) {
            $kitabData['publication_status'] = 'draft';
            $kitabData['published_at'] = null;
            $kitabData['published_by'] = null;
            $kitabData['reviewed_at'] = null;
            $kitabData['reviewed_by'] = null;

            $kitab = Kitab::create($kitabData);

            $this->createRevision($kitab, 'created', null, $kitab->toArray(), $actorId, $note);
            $this->logAudit('kitab_created_draft', $kitab, $actorId, null, $kitab->toArray());

            return $kitab;
        });
    }

    /**
     * Update kitab with revision tracking.
     * If published kitab changes, status automatically becomes review.
     */
    public function updateWithRevision(Kitab $kitab, array $payload, int $actorId, ?string $note = null): Kitab
    {
        return DB::transaction(function () use ($kitab, $payload, $actorId, $note) {
            $old = $kitab->toArray();
            $oldStatus = $kitab->publication_status;

            $kitab->fill($payload);

            // Any change to published content moves it back to review gate.
            if ($oldStatus === 'published' && $kitab->isDirty()) {
                $kitab->publication_status = 'review';
                $kitab->reviewed_at = now();
                $kitab->reviewed_by = $actorId;
                $kitab->status_note = $note ?: 'Perubahan konten dari versi published, menunggu review ulang.';
            }

            $kitab->save();
            $kitab->refresh();

            $new = $kitab->toArray();
            $changedFields = $this->detectChangedFields(
                Arr::except($old, ['updated_at']),
                Arr::except($new, ['updated_at'])
            );

            $this->createRevision($kitab, 'updated', $old, $new, $actorId, $note, $changedFields);
            $this->logAudit('kitab_updated', $kitab, $actorId, $old, $new);

            return $kitab;
        });
    }

    public function submitForReview(Kitab $kitab, int $actorId, ?string $note = null): Kitab
    {
        return $this->transitionStatus($kitab, $actorId, 'draft', 'review', 'submitted_for_review', $note);
    }

    public function publish(Kitab $kitab, int $actorId, ?string $note = null): Kitab
    {
        $this->assertPublishQualityGate($kitab, $actorId);
        return $this->transitionStatus($kitab, $actorId, 'review', 'published', 'published', $note);
    }

    public function returnToDraft(Kitab $kitab, int $actorId, ?string $note = null): Kitab
    {
        return DB::transaction(function () use ($kitab, $actorId, $note) {
            $fromStatus = $kitab->publication_status;
            if (!in_array($fromStatus, ['review', 'published'], true)) {
                throw new InvalidArgumentException("Transisi status {$fromStatus} -> draft tidak diizinkan.");
            }

            $old = $kitab->toArray();

            $kitab->publication_status = 'draft';
            $kitab->status_note = $note;
            $kitab->published_at = null;
            $kitab->published_by = null;
            $kitab->save();
            $kitab->refresh();

            $this->createRevision(
                $kitab,
                'returned_to_draft',
                $old,
                $kitab->toArray(),
                $actorId,
                $note,
                ['publication_status', 'status_note', 'published_at', 'published_by']
            );
            $this->logAudit('kitab_returned_to_draft', $kitab, $actorId, $old, $kitab->toArray());

            return $kitab;
        });
    }

    private function transitionStatus(
        Kitab $kitab,
        int $actorId,
        string $from,
        string $to,
        string $revisionAction,
        ?string $note = null
    ): Kitab {
        return DB::transaction(function () use ($kitab, $actorId, $from, $to, $revisionAction, $note) {
            if ($kitab->publication_status !== $from) {
                throw new InvalidArgumentException("Transisi status {$kitab->publication_status} -> {$to} tidak diizinkan.");
            }

            $old = $kitab->toArray();
            $kitab->publication_status = $to;
            $kitab->status_note = $note;

            if ($to === 'review') {
                $kitab->reviewed_at = now();
                $kitab->reviewed_by = $actorId;
            }
            if ($to === 'published') {
                $kitab->published_at = now();
                $kitab->published_by = $actorId;
            }

            $kitab->save();
            $kitab->refresh();

            $this->createRevision(
                $kitab,
                $revisionAction,
                $old,
                $kitab->toArray(),
                $actorId,
                $note,
                ['publication_status', 'status_note']
            );
            $this->logAudit('kitab_status_changed', $kitab, $actorId, $old, $kitab->toArray());

            return $kitab;
        });
    }

    private function createRevision(
        Kitab $kitab,
        string $action,
        ?array $oldData,
        ?array $newData,
        int $actorId,
        ?string $note = null,
        array $changedFields = []
    ): KitabRevision {
        $nextVersion = ((int) KitabRevision::where('kitab_id', $kitab->id_kitab)->max('version_no')) + 1;

        return KitabRevision::create([
            'kitab_id' => $kitab->id_kitab,
            'version_no' => $nextVersion,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'changed_fields' => $changedFields,
            'old_file_pdf' => $oldData['file_pdf'] ?? null,
            'old_cover' => $oldData['cover'] ?? null,
            'actor_id' => $actorId,
            'note' => $note,
        ]);
    }

    private function logAudit(string $action, Kitab $kitab, int $actorId, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::log($action, $kitab, $actorId, $oldValues, $newValues);
    }

    private function detectChangedFields(array $oldData, array $newData): array
    {
        $keys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
        $changed = [];

        foreach ($keys as $key) {
            $oldValue = $oldData[$key] ?? null;
            $newValue = $newData[$key] ?? null;

            if ($this->normalizeForCompare($oldValue) !== $this->normalizeForCompare($newValue)) {
                $changed[] = (string) $key;
            }
        }

        return $changed;
    }

    private function normalizeForCompare($value): string
    {
        if (is_array($value)) {
            $normalized = $this->sortRecursive($value);
            return (string) json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_object($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($value === null) {
            return '__null__';
        }

        if (is_bool($value)) {
            return $value ? '__true__' : '__false__';
        }

        return (string) $value;
    }

    private function sortRecursive(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->sortRecursive($item);
            }
        }

        if ($this->isAssocArray($value)) {
            ksort($value);
        }

        return $value;
    }

    private function isAssocArray(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function assertPublishQualityGate(Kitab $kitab, int $actorId): void
    {
        $issues = [];

        if (mb_strlen(trim((string) $kitab->judul)) < 3) {
            $issues[] = 'Judul minimal 3 karakter.';
        }

        if (mb_strlen(trim((string) $kitab->penulis)) < 3) {
            $issues[] = 'Penulis minimal 3 karakter.';
        }

        $descriptionLength = mb_strlen(trim(strip_tags((string) $kitab->deskripsi)));
        if ($descriptionLength < 40) {
            $issues[] = 'Deskripsi minimal 40 karakter.';
        }

        if (trim((string) $kitab->kategori) === '') {
            $issues[] = 'Kategori wajib diisi.';
        }

        if (trim((string) $kitab->bahasa) === '') {
            $issues[] = 'Bahasa wajib diisi.';
        }

        $issues = array_merge($issues, $this->validateStoredFile($kitab->file_pdf, 'pdf', 'File PDF', ['pdf']));
        $issues = array_merge($issues, $this->validateStoredFile($kitab->cover, 'cover', 'Cover', ['jpg', 'jpeg', 'png', 'webp']));

        if (!empty($issues)) {
            Log::channel('moderation')->warning('Kitab publish blocked by quality gate', [
                'kitab_id' => $kitab->id_kitab,
                'actor_id' => $actorId,
                'status' => $kitab->publication_status,
                'issues' => $issues,
            ]);

            throw new InvalidArgumentException('Quality gate publish gagal: ' . implode(' ', $issues));
        }
    }

    private function validateStoredFile(?string $fileName, string $folder, string $label, array $allowedExtensions): array
    {
        $issues = [];
        $fileName = trim((string) $fileName);

        if ($fileName === '') {
            return ["{$label} wajib tersedia."];
        }

        if (str_starts_with($fileName, 'placeholder_')) {
            $issues[] = "{$label} masih placeholder.";
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            $issues[] = "{$label} harus berformat " . strtoupper(implode('/', $allowedExtensions)) . '.';
        }

        $filePath = public_path(trim($folder, '/\\') . DIRECTORY_SEPARATOR . ltrim($fileName, '/\\'));
        if (!is_file($filePath) || !is_readable($filePath)) {
            $issues[] = "{$label} tidak ditemukan di server.";
            return $issues;
        }

        if ((int) filesize($filePath) <= 0) {
            $issues[] = "{$label} rusak (ukuran 0 byte).";
        }

        return $issues;
    }
}
