<?php

namespace Tests\Feature;

use App\Models\Kitab;
use Tests\Feature\Concerns\ApiSchemaSetup;
use Tests\TestCase;

class RequestTraceMiddlewareTest extends TestCase
{
    use ApiSchemaSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useSqliteMemoryOrSkip();
        $this->resetTables(['kitab']);
        $this->createKitabTable();

        Kitab::create([
            'judul' => 'Kitab Trace',
            'penulis' => 'Penulis Trace',
            'deskripsi' => 'Deskripsi untuk memastikan endpoint kitab bisa merespons normal.',
            'kategori' => 'Aqidah',
            'bahasa' => 'Indonesia',
            'file_pdf' => 'trace.pdf',
            'cover' => 'trace.jpg',
            'publication_status' => 'published',
        ]);
    }

    public function test_api_response_contains_generated_request_id_header(): void
    {
        $response = $this->getJson('/api/v1/kitab');
        $response->assertStatus(200);

        $requestId = (string) $response->headers->get('X-Request-Id');
        $this->assertNotSame('', trim($requestId));
    }

    public function test_api_reuses_request_id_from_header_when_provided(): void
    {
        $response = $this
            ->withHeaders(['X-Request-Id' => 'trace-fixed-id-123'])
            ->getJson('/api/v1/kitab');

        $response->assertStatus(200);
        $this->assertSame('trace-fixed-id-123', $response->headers->get('X-Request-Id'));
    }
}
