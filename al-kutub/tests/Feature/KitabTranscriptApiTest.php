<?php

namespace Tests\Feature;

use App\Models\Kitab;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class KitabTranscriptApiTest extends TestCase
{
    use ApiSchemaSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables(['kitab_transcript_segments', 'kitab', 'users']);
        $this->createUsersTable();
        $this->createKitabTable();
        $this->createKitabTranscriptSegmentsTable();
    }

    public function test_public_transcript_api_returns_summary_and_page_map(): void
    {
        $kitab = Kitab::create([
            'judul' => 'Kitab Audio',
            'penulis' => 'Penulis',
            'deskripsi' => 'Deskripsi fallback',
            'kategori' => 'Hadits',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'audio.pdf',
            'cover' => 'audio.jpg',
            'publication_status' => 'published',
        ]);

        DB::table('kitab_transcript_segments')->insert([
            [
                'kitab_id' => $kitab->id_kitab,
                'section_key' => 'summary',
                'transcript_type' => 'summary',
                'title' => 'Ringkasan',
                'content' => "Ringkasan kitab yang sudah dibersihkan.\n\n",
                'language' => 'Indonesia',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kitab_id' => $kitab->id_kitab,
                'section_key' => 'page-1',
                'transcript_type' => 'page',
                'title' => 'Halaman 1',
                'content' => "1\nIsi halaman pertama yang rapi.",
                'language' => 'Indonesia',
                'page_start' => 1,
                'page_end' => 1,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson("/api/v1/kitab/{$kitab->id_kitab}/transcript");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kitabId', $kitab->id_kitab)
            ->assertJsonPath('data.summaryText', 'Ringkasan kitab yang sudah dibersihkan.')
            ->assertJsonPath('data.hasTranscript', true)
            ->assertJsonPath('data.hasSummaryTranscript', true)
            ->assertJsonPath('data.hasPageTranscript', true)
            ->assertJsonPath('data.pageMap.1', 'Isi halaman pertama yang rapi.')
            ->assertJsonCount(2, 'data.segments');
    }

    public function test_public_transcript_api_hides_unpublished_kitab(): void
    {
        $kitab = Kitab::create([
            'judul' => 'Draft Audio',
            'penulis' => 'Penulis',
            'deskripsi' => 'Deskripsi draft',
            'kategori' => 'Tauhid',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'draft.pdf',
            'cover' => 'draft.jpg',
            'publication_status' => 'draft',
        ]);

        $this->getJson("/api/v1/kitab/{$kitab->id_kitab}/transcript")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
