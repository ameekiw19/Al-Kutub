<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationSettingsApiTest extends TestCase
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

        Schema::dropIfExists('user_notification_settings');
        Schema::dropIfExists('audit_logs');
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

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('enable_notifications')->default(true);
            $table->boolean('new_book_notifications')->default(true);
            $table->boolean('update_notifications')->default(true);
            $table->boolean('reminder_notifications')->default(true);
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_start', 5)->default('22:00');
            $table->string('quiet_hours_end', 5)->default('08:00');
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);
            $table->boolean('led_enabled')->default(true);
            $table->string('notification_style', 20)->default('BASIC');
            $table->text('categories')->nullable();
            $table->timestamps();
        });
    }

    public function test_notification_settings_requires_authentication()
    {
        $response = $this->getJson('/api/v1/settings/notifications');
        $response->assertStatus(401);
    }

    public function test_get_notification_settings_returns_default_for_authenticated_user()
    {
        $user = User::create([
            'username' => 'notif-user',
            'email' => 'notif-user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/settings/notifications');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enable_notifications', true)
            ->assertJsonPath('data.quiet_hours_start', '22:00')
            ->assertJsonPath('data.notification_style', 'BASIC');
    }

    public function test_update_notification_settings_persists_values()
    {
        $user = User::create([
            'username' => 'notif-update',
            'email' => 'notif-update@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'enable_notifications' => true,
            'new_book_notifications' => false,
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '21:30',
            'quiet_hours_end' => '06:30',
            'notification_style' => 'SILENT',
            'categories' => [
                'islamic' => true,
                'education' => false,
            ],
        ];

        $update = $this->putJson('/api/v1/settings/notifications', $payload);
        $update->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.new_book_notifications', false)
            ->assertJsonPath('data.notification_style', 'SILENT')
            ->assertJsonPath('data.quiet_hours_start', '21:30');

        $get = $this->getJson('/api/v1/settings/notifications');
        $get->assertStatus(200)
            ->assertJsonPath('data.new_book_notifications', false)
            ->assertJsonPath('data.quiet_hours_enabled', true)
            ->assertJsonPath('data.categories.education', false);
    }

    public function test_invalid_notification_style_returns_validation_error()
    {
        $user = User::create([
            'username' => 'notif-invalid',
            'email' => 'notif-invalid@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/settings/notifications', [
            'notification_style' => 'LOUD',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
