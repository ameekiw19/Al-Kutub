<?php

namespace Tests\Feature;

use App\Models\TwoFactorAuth;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TwoFactorApiTest extends TestCase
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

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('two_factor_auths');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
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

        Schema::create('two_factor_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('secret_key', 32)->nullable();
            $table->json('backup_codes')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_login_verify_2fa_accepts_valid_backup_code()
    {
        $user = $this->createUserWith2FA(['AB12CD34', 'EF56GH78']);

        $loginResponse = $this->postJson('/api/v1/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_2fa', true);

        $tempToken = $loginResponse->json('data.temp_token');

        $verifyResponse = $this->postJson('/api/v1/login/verify-2fa', [
            'user_id' => $user->id,
            'code' => 'AB12CD34',
            'temp_token' => $tempToken,
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_2fa', false);

        $this->assertNotEmpty($verifyResponse->json('data.token'));

        $remainingCodes = $user->fresh()->twoFactorAuth->getBackupCodesArray();
        $this->assertSame(['EF56GH78'], $remainingCodes);
    }

    public function test_backup_code_can_only_be_used_once_for_login_verification()
    {
        $user = $this->createUserWith2FA(['AB12CD34', 'EF56GH78']);

        $firstLogin = $this->postJson('/api/v1/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $firstToken = $firstLogin->json('data.temp_token');

        $firstVerify = $this->postJson('/api/v1/login/verify-2fa', [
            'user_id' => $user->id,
            'code' => 'AB12CD34',
            'temp_token' => $firstToken,
        ]);
        $firstVerify->assertStatus(200)->assertJsonPath('success', true);

        $secondLogin = $this->postJson('/api/v1/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $secondToken = $secondLogin->json('data.temp_token');

        $secondVerify = $this->postJson('/api/v1/login/verify-2fa', [
            'user_id' => $user->id,
            'code' => 'AB12CD34',
            'temp_token' => $secondToken,
        ]);

        $secondVerify->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_status_returns_integer_backup_codes_count()
    {
        $user = $this->createUserWith2FA(['AB12CD34', 'EF56GH78', 'ZX90CV12']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/2fa/status');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.backup_codes_count', 3);

        $this->assertIsInt($response->json('data.backup_codes_count'));
    }

    public function test_get_backup_codes_returns_expected_contract()
    {
        $user = $this->createUserWith2FA(['AB12CD34', 'EF56GH78']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/2fa/backup-codes');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.remaining_count', 2)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'backup_codes',
                    'remaining_count',
                ],
            ]);

        $this->assertNotEmpty($response->json('message'));
    }

    public function test_verify_backup_code_accepts_alphanumeric_and_rejects_invalid_format()
    {
        $user = $this->createUserWith2FA(['AB12CD34', 'EF56GH78']);
        Sanctum::actingAs($user);

        $valid = $this->postJson('/api/v1/2fa/verify-backup-code', [
            'code' => 'AB12CD34',
        ]);

        $valid->assertStatus(200)
            ->assertJsonPath('success', true);

        $invalid = $this->postJson('/api/v1/2fa/verify-backup-code', [
            'code' => 'AB12-CD3',
        ]);

        $invalid->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    private function createUserWith2FA(array $backupCodes): User
    {
        $user = User::create([
            'username' => 'twofa-user-' . uniqid(),
            'email' => uniqid('twofa-', true) . '@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        TwoFactorAuth::create([
            'user_id' => $user->id,
            'secret_key' => TwoFactorAuth::generateSecretKey(),
            'backup_codes' => $backupCodes,
            'enabled_at' => now(),
            'is_enabled' => true,
        ]);

        return $user;
    }
}
