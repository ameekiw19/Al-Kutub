<?php

namespace Tests\Feature;

use App\Models\Kitab;
use App\Models\User;
use App\Services\KitabPublicationService;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class KitabRevisionHistoryTest extends TestCase
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

    public function test_revision_endpoint_returns_workflow_revisions()
    {
        $admin = User::create([
            'username' => 'admin-revision',
            'email' => 'admin-revision@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
        Sanctum::actingAs($admin);

        $kitab = Kitab::create([
            'judul' => 'Kitab Revisi',
            'penulis' => 'Penulis Revisi',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Hadits',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'rev.pdf',
            'cover' => 'rev.jpg',
            'publication_status' => 'draft',
        ]);

        $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/submit-review")->assertStatus(200);
        $this->postJson("/api/v1/admin/kitab/{$kitab->id_kitab}/return-draft")->assertStatus(200);

        $response = $this->getJson("/api/v1/admin/kitab/{$kitab->id_kitab}/revisions");
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertContains($data[0]['action'], ['submitted_for_review', 'returned_to_draft']);
    }

    public function test_update_with_revision_handles_json_field_without_array_to_string_error()
    {
        $admin = User::create([
            'username' => 'admin-revision-json',
            'email' => 'admin-revision-json@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $kitab = Kitab::create([
            'judul' => 'Kitab JSON',
            'penulis' => 'Penulis JSON',
            'deskripsi' => 'Deskripsi untuk memastikan update revision aman untuk field array.',
            'kategori' => 'Hadits',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'json.pdf',
            'cover' => 'json.jpg',
            'viewed_by' => [5],
            'publication_status' => 'review',
        ]);

        $service = app(KitabPublicationService::class);
        $updated = $service->updateWithRevision(
            $kitab,
            ['judul' => 'Kitab JSON Update'],
            (int) $admin->id
        );

        $this->assertSame('Kitab JSON Update', $updated->judul);
        $this->assertDatabaseHas('kitab_revisions', [
            'kitab_id' => $kitab->id_kitab,
            'action' => 'updated',
        ], 'sqlite');
    }
}
