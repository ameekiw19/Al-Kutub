<?php

namespace Tests\Feature;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionManagementApiTest extends TestCase
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

        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->boolean('is_verified_by_admin')->default(true);
            $table->rememberToken();
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

        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_id', 120)->nullable();
            $table->string('device_name', 120)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('token_hash', 128)->unique();
            $table->unsignedBigInteger('access_token_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason', 50)->nullable();
            $table->unsignedBigInteger('replaced_by_token_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_current_session_can_be_resolved_by_device_id_header()
    {
        [$user, $bearer] = $this->createAuthenticatedUser();

        $session = RefreshToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-alpha',
            'device_name' => 'Pixel Test',
            'token_hash' => hash('sha256', 'refresh-alpha'),
            'access_token_id' => null,
            'expires_at' => now()->addDays(7),
            'last_used_at' => now(),
            'last_seen_at' => now(),
        ]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$bearer}")
            ->withHeader('X-Device-Id', 'device-alpha')
            ->getJson('/api/v1/sessions/current');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $session->id)
            ->assertJsonPath('data.is_current', true);
    }

    public function test_revoke_session_rejects_current_active_session()
    {
        [$user, $bearer, $accessTokenId] = $this->createAuthenticatedUser(true);

        $currentSession = RefreshToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-current',
            'device_name' => 'Pixel Current',
            'token_hash' => hash('sha256', 'refresh-current'),
            'access_token_id' => $accessTokenId,
            'expires_at' => now()->addDays(7),
            'last_used_at' => now(),
            'last_seen_at' => now(),
        ]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$bearer}")
            ->deleteJson('/api/v1/sessions/' . $currentSession->id);

        $response->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_revoke_session_succeeds_for_other_session()
    {
        [$user, $bearer, $accessTokenId] = $this->createAuthenticatedUser(true);

        RefreshToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-current',
            'device_name' => 'Pixel Current',
            'token_hash' => hash('sha256', 'refresh-current-2'),
            'access_token_id' => $accessTokenId,
            'expires_at' => now()->addDays(7),
            'last_used_at' => now(),
            'last_seen_at' => now(),
        ]);

        $otherSession = RefreshToken::create([
            'user_id' => $user->id,
            'device_id' => 'device-other',
            'device_name' => 'Tablet Other',
            'token_hash' => hash('sha256', 'refresh-other'),
            'access_token_id' => null,
            'expires_at' => now()->addDays(7),
            'last_used_at' => now(),
            'last_seen_at' => now(),
        ]);

        $response = $this
            ->withHeader('Authorization', "Bearer {$bearer}")
            ->deleteJson('/api/v1/sessions/' . $otherSession->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotNull($otherSession->fresh()->revoked_at);
        $this->assertSame('user_revoked', $otherSession->fresh()->revoked_reason);
    }

    /**
     * @return array{0: User, 1: string, 2?: int}
     */
    private function createAuthenticatedUser(bool $withAccessTokenId = false): array
    {
        $user = User::create([
            'username' => 'session-user-' . uniqid(),
            'email' => uniqid('session-', true) . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'is_verified_by_admin' => true,
            'email_verified_at' => now(),
        ]);

        $tokenResult = $user->createToken('android');
        $bearer = $tokenResult->plainTextToken;

        if ($withAccessTokenId) {
            return [$user, $bearer, (int) $tokenResult->accessToken->id];
        }

        return [$user, $bearer];
    }
}

