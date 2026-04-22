<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_public_kitab_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/kitab');
        $this->assertNotEquals(404, $response->status());
    }

    public function test_login_validation_returns_422_when_required_fields_missing()
    {
        $response = $this->postJson('/api/v1/login', []);
        $response->assertStatus(422);
    }

    public function test_history_requires_authentication()
    {
        $response = $this->getJson('/api/v1/history');
        $response->assertStatus(401);
    }

    public function test_search_suggestions_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/search/suggestions?query=fiqih');
        $this->assertNotEquals(404, $response->status());
    }
}
