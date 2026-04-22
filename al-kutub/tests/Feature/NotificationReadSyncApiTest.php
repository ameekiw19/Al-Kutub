<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationReadSyncApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('notification_user_reads');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->text('deskripsi')->nullable();
            $table->string('phone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->string('action_url')->nullable();
            $table->text('data')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'notification_id'], 'notification_user_unique');
        });
    }

    public function test_unread_count_requires_authentication()
    {
        $response = $this->getJson('/api/v1/notifications/unread-count');
        $response->assertStatus(401);
    }

    public function test_mark_as_read_updates_only_current_user()
    {
        $userA = User::create([
            'username' => 'user-a',
            'email' => 'user-a@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $userB = User::create([
            'username' => 'user-b',
            'email' => 'user-b@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $notification = AppNotification::create([
            'title' => 'New Book',
            'message' => 'A new kitab is available',
            'type' => 'new_kitab',
        ]);

        Sanctum::actingAs($userA);
        $markResponse = $this->postJson("/api/v1/notifications/{$notification->id}/read");
        $markResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 0);

        $this->assertDatabaseHas('notification_user_reads', [
            'user_id' => $userA->id,
            'notification_id' => $notification->id,
        ], 'sqlite');

        Sanctum::actingAs($userB);
        $countResponse = $this->getJson('/api/v1/notifications/unread-count');
        $countResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_mark_all_as_read_marks_all_unread_and_is_idempotent()
    {
        $user = User::create([
            'username' => 'user-mark-all',
            'email' => 'user-mark-all@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        AppNotification::create([
            'title' => 'Notif 1',
            'message' => 'Message 1',
            'type' => 'info',
        ]);
        AppNotification::create([
            'title' => 'Notif 2',
            'message' => 'Message 2',
            'type' => 'info',
        ]);
        AppNotification::create([
            'title' => 'Notif 3',
            'message' => 'Message 3',
            'type' => 'promo',
        ]);

        Sanctum::actingAs($user);

        $firstCall = $this->postJson('/api/v1/notifications/read-all');
        $firstCall->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.marked_count', 3)
            ->assertJsonPath('data.unread_count', 0);

        $this->assertEquals(3, DB::table('notification_user_reads')->where('user_id', $user->id)->count());

        $secondCall = $this->postJson('/api/v1/notifications/read-all');
        $secondCall->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.marked_count', 0)
            ->assertJsonPath('data.unread_count', 0);
    }
}
