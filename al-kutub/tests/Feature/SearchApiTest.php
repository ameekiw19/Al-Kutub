<?php

namespace Tests\Feature;

use Tests\TestCase;

class SearchApiTest extends TestCase
{
    public function test_suggestions_with_short_query_returns_empty_list()
    {
        $response = $this->getJson('/api/v1/search/suggestions?query=a');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_clear_search_history_requires_authentication()
    {
        $response = $this->deleteJson('/api/v1/search/history');
        $response->assertStatus(401);
    }

    public function test_search_rejects_invalid_sort_by()
    {
        $response = $this->getJson('/api/v1/kitab/search?search=fiqih&sort_by=invalid');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_search_rejects_invalid_year_range()
    {
        $response = $this->getJson('/api/v1/kitab/search?min_year=2025&max_year=2020');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
