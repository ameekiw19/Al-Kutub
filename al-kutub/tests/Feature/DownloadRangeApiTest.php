<?php

namespace Tests\Feature;

use App\Models\Kitab;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class DownloadRangeApiTest extends TestCase
{
    use ApiSchemaSetup;

    private string $testPdfPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables(['kitab', 'users']);
        $this->createUsersTable();
        $this->createKitabTable();

        $dir = public_path('pdf');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->testPdfPath = $dir . '/range-test.pdf';
        file_put_contents($this->testPdfPath, str_repeat('ABCDEFGHIJ', 20)); // 200 bytes
    }

    protected function tearDown(): void
    {
        if (isset($this->testPdfPath) && file_exists($this->testPdfPath)) {
            @unlink($this->testPdfPath);
        }
        parent::tearDown();
    }

    public function test_download_supports_range_and_does_not_increment_counter_for_resume()
    {
        $user = User::create([
            'username' => 'user-range',
            'email' => 'user-range@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
        Sanctum::actingAs($user);

        $kitab = Kitab::create([
            'judul' => 'Range Kitab',
            'penulis' => 'Penulis Range',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'Fiqih',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'range-test.pdf',
            'cover' => 'range.jpg',
            'publication_status' => 'published',
            'downloads' => 0,
        ]);

        $partial = $this->get("/api/v1/kitab/{$kitab->id_kitab}/download", [
            'Range' => 'bytes=0-9',
        ]);
        $partial->assertStatus(206);
        $this->assertSame('bytes', $partial->headers->get('Accept-Ranges'));
        $this->assertSame('bytes 0-9/200', $partial->headers->get('Content-Range'));
        $this->assertSame(10, strlen($partial->streamedContent()));

        $kitab->refresh();
        $this->assertSame(0, $kitab->downloads);

        $full = $this->get("/api/v1/kitab/{$kitab->id_kitab}/download");
        $full->assertStatus(200);
        $kitab->refresh();
        $this->assertSame(1, $kitab->downloads);
    }
}

