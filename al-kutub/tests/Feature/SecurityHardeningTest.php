<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_phpinfo_route_not_available_outside_local_environment()
    {
        $response = $this->get('/phpinfo');
        $response->assertStatus(404);
    }

    public function test_admin_kitab_stats_requires_authentication()
    {
        $response = $this->getJson('/api/v1/admin/kitab/stats');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_admin_kitab_stats()
    {
        $user = new User();
        $user->id = 999;
        $user->username = 'demo-user';
        $user->email = 'demo-user@example.com';
        $user->role = 'user';

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/kitab/stats');
        $response->assertStatus(403);
    }

    public function test_notifications_unread_count_requires_authentication()
    {
        $response = $this->getJson('/api/v1/notifications/unread-count');
        $response->assertStatus(401);
    }
}
