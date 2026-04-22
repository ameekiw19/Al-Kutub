<?php

namespace Tests\Feature;

use App\Models\Kitab;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class KitabPublicationWorkflowTest extends TestCase
{
    use ApiSchemaSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables([
            'kitab_revisions',
            'audit_logs',
            'notification_user_reads',
            'app_notifications',
            'fcm_tokens',
            'kitab',
            'users',
        ]);

        $this->createUsersTable();
        $this->createKitabTable();
        $this->createKitabRevisionsTable();
        $this->createAuditLogsTable();
        $this->createNotificationTables();
        $this->createFcmTokensTable();
    }

    protected function tearDown(): void
    {
        $this->cleanupPublicationTestAssets();
        parent::tearDown();
    }

    public function test_admin_can_transition_draft_review_published_and_back_to_draft()
    {
        $admin = User::create([
            'username' => 'admin-workflow',
            'email' => 'admin-workflow@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        Sanctum::actingAs($admin);

        [$pdfFile, $coverFile] = $this->createPublishReadyAssets();

        $kitab = Kitab::create([
            'judul' => 'Kitab Workflow',
            'penulis' => 'Penulis A',
            'deskripsi' => 'Deskripsi lengkap untuk quality gate publish agar transisi status valid.',
            'kategori' => 'Fiqih',
            'bahasa' => 'Indonesia',
            'file_pdf' => $pdfFile,
            'cover' => $coverFile,
            'publication_status' => 'draft',
        ]);

        $submit = $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/submit-review");
        $submit->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.publication_status', 'review');

        $publish = $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/publish");
        $publish->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.publication_status', 'published');

        $returnDraft = $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/return-draft");
        $returnDraft->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.publication_status', 'draft');

        $this->assertDatabaseHas('kitab', [
            'id_kitab' => $kitab->id_kitab,
            'publication_status' => 'draft',
        ], 'sqlite');

        $this->assertSame(3, DB::table('kitab_revisions')->where('kitab_id', $kitab->id_kitab)->count());
    }

    public function test_publish_is_blocked_when_quality_gate_is_not_satisfied()
    {
        $admin = User::create([
            'username' => 'admin-quality-gate',
            'email' => 'admin-quality-gate@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        Sanctum::actingAs($admin);

        $kitab = Kitab::create([
            'judul' => 'AB',
            'penulis' => 'CD',
            'deskripsi' => 'Singkat',
            'kategori' => 'Fiqih',
            'bahasa' => 'Indonesia',
            'file_pdf' => null,
            'cover' => null,
            'publication_status' => 'review',
        ]);

        $publish = $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/publish");
        $publish->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJson(fn ($json) => $json->where('success', false)->etc());

        $this->assertStringContainsString(
            'Quality gate publish gagal',
            (string) $publish->json('message')
        );

        $this->assertDatabaseHas('kitab', [
            'id_kitab' => $kitab->id_kitab,
            'publication_status' => 'review',
            'published_at' => null,
            'published_by' => null,
        ], 'sqlite');
    }

    private function createPublishReadyAssets(): array
    {
        $pdfDir = public_path('pdf');
        $coverDir = public_path('cover');
        File::ensureDirectoryExists($pdfDir);
        File::ensureDirectoryExists($coverDir);

        $token = uniqid('pubtest_', true);
        $pdfFile = "{$token}.pdf";
        $coverFile = "{$token}.jpg";

        File::put($pdfDir . DIRECTORY_SEPARATOR . $pdfFile, '%PDF-1.4 publication workflow test');
        File::put($coverDir . DIRECTORY_SEPARATOR . $coverFile, 'fake-image-bytes');

        return [$pdfFile, $coverFile];
    }

    private function cleanupPublicationTestAssets(): void
    {
        foreach (['pdf', 'cover'] as $folder) {
            $path = public_path($folder);
            if (!is_dir($path)) {
                continue;
            }

            foreach (glob($path . DIRECTORY_SEPARATOR . 'pubtest_*.pdf') ?: [] as $filePath) {
                @unlink($filePath);
            }
            foreach (glob($path . DIRECTORY_SEPARATOR . 'pubtest_*.jpg') ?: [] as $filePath) {
                @unlink($filePath);
            }
        }
    }
}
