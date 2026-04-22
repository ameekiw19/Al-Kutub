<?php

namespace Tests\Feature;

use App\Models\Kitab;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class ReadingNoteIdempotencyTest extends TestCase
{
    use ApiSchemaSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables(['reading_notes', 'kitab', 'users']);
        $this->createUsersTable();
        $this->createKitabTable();
        $this->createReadingNotesTable();
    }

    public function test_create_reading_note_is_idempotent_with_client_request_id()
    {
        $user = User::create([
            'username' => 'user-note',
            'email' => 'user-note@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
        Sanctum::actingAs($user);

        $kitab = Kitab::create([
            'judul' => 'Kitab Note',
            'penulis' => 'Penulis Note',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Sirah',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'note.pdf',
            'cover' => 'note.jpg',
            'publication_status' => 'published',
        ]);

        $payload = [
            'kitab_id' => $kitab->id_kitab,
            'note_content' => 'Catatan pertama',
            'page_number' => 5,
            'note_color' => '#FFFF00',
            'is_private' => true,
            'client_request_id' => 'REQSYNC01',
        ];

        $first = $this->postJson('/api/v1/reading-notes', $payload);
        $first->assertStatus(201)
            ->assertJsonPath('success', true);

        $second = $this->postJson('/api/v1/reading-notes', $payload);
        $second->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSame(
            $first->json('data.id'),
            $second->json('data.id')
        );

        $this->assertSame(
            1,
            DB::table('reading_notes')
                ->where('user_id', $user->id)
                ->where('client_request_id', 'REQSYNC01')
                ->count()
        );
    }
}

