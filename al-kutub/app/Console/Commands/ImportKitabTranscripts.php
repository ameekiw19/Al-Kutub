<?php

namespace App\Console\Commands;

use App\Models\Kitab;
use App\Services\KitabTranscriptImportService;
use Illuminate\Console\Command;

class ImportKitabTranscripts extends Command
{
    protected $signature = 'kitab:import-transcripts
                            {ids?* : ID kitab yang ingin diimport}
                            {--all : Import semua kitab}
                            {--force : Hapus transcript lama dan generate ulang}
                            {--only-missing : Hanya kitab tanpa transcript}
                            {--limit=0 : Batas jumlah kitab saat import bulk}';

    protected $description = 'Generate transcript kitab otomatis dari file PDF.';

    public function handle(KitabTranscriptImportService $importService): int
    {
        $ids = collect((array) $this->argument('ids'))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        $query = Kitab::query()->orderBy('id_kitab');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id_kitab', $ids->all());
        } elseif (!$this->option('all')) {
            $this->warn('Tidak ada ID kitab yang dipilih. Menggunakan semua kitab yang tersedia.');
        }

        if ($this->option('only-missing')) {
            $query->whereDoesntHave('transcriptSegments');
        }

        $limit = max(0, (int) $this->option('limit'));
        if ($limit > 0) {
            $query->limit($limit);
        }

        $kitabs = $query->get();

        if ($kitabs->isEmpty()) {
            $this->warn('Tidak ada kitab yang cocok untuk diimport.');
            return self::SUCCESS;
        }

        $this->info("Menyiapkan import transcript untuk {$kitabs->count()} kitab.");
        $bar = $this->output->createProgressBar($kitabs->count());
        $bar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($kitabs as $kitab) {
            try {
                $result = $importService->import($kitab, (bool) $this->option('force'));

                if (($result['status'] ?? '') === 'skipped') {
                    $skipped++;
                    $this->newLine();
                    $this->line("[SKIP] #{$kitab->id_kitab} {$kitab->judul} - {$result['message']}");
                } else {
                    $success++;
                    $this->newLine();
                    $this->info("[OK] #{$kitab->id_kitab} {$kitab->judul} - {$result['total_segments']} segmen ({$result['page_segments']} halaman, {$result['chapter_segments']} bab, audio ID {$result['translation_audio_ready']}, audio Arab {$result['arabic_audio_ready']}).");
                }
            } catch (\Throwable $error) {
                $failed++;
                $this->newLine();
                $this->error("[FAIL] #{$kitab->id_kitab} {$kitab->judul} - {$error->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Berhasil', $success],
                ['Skip', $skipped],
                ['Gagal', $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
