<?php

namespace Tests\Feature;

use App\Models\Kitab;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class PublishedOnlyVisibilityTest extends TestCase
{
    use ApiSchemaSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables(['kitab', 'users']);
        $this->createUsersTable();
        $this->createKitabTable();
    }

    public function test_public_api_only_exposes_published_kitab()
    {
        $draft = Kitab::create([
            'judul' => 'Draft Kitab',
            'penulis' => 'Penulis Draft',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Tauhid',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'draft.pdf',
            'cover' => 'draft.jpg',
            'publication_status' => 'draft',
        ]);

        $published = Kitab::create([
            'judul' => 'Published Kitab',
            'penulis' => 'Penulis Publish',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Aqidah',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'published.pdf',
            'cover' => 'published.jpg',
            'publication_status' => 'published',
        ]);

        $list = $this->getJson('/api/v1/kitab');
        $list->assertStatus(200)
            ->assertJsonPath('success', true);

        $ids = collect($list->json('data'))->pluck('idKitab')->all();
        $this->assertContains($published->id_kitab, $ids);
        $this->assertNotContains($draft->id_kitab, $ids);

        $this->getJson("/api/v1/kitab/{$draft->id_kitab}")->assertStatus(404);
        $this->getJson("/api/v1/kitab/{$published->id_kitab}")->assertStatus(200);
    }
}

