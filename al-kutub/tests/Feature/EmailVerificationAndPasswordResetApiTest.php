<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use App\Support\VerificationTokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationAndPasswordResetApiTest extends TestCase
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
        Schema::dropIfExists('password_resets');
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

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
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

    public function test_register_creates_unverified_user_and_triggers_verification_notification()
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/register', [
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_email_verification', true)
            ->assertJsonPath('data.token', null);

        $user = User::where('username', 'new-user')->firstOrFail();
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    public function test_login_unverified_returns_requires_email_verification_without_auth_token()
    {
        $user = User::create([
            'username' => 'pending-user',
            'email' => 'pending-user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'username' => $user->username,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.requires_email_verification', true)
            ->assertJsonPath('data.token', null);
    }

    public function test_public_signed_verify_route_marks_email_as_verified()
    {
        $user = User::create([
            'username' => 'verify-user',
            'email' => 'verify-user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify.public',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_status_changes_after_user_verifies_email()
    {
        $user = User::create([
            'username' => 'status-user',
            'email' => 'status-user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        $challengeToken = VerificationTokenService::issue($user);

        $before = $this->postJson('/api/v1/email/verification/status', [
            'verification_token' => $challengeToken,
        ]);
        $before->assertStatus(200)
            ->assertJsonPath('data.verified', false);

        $user->markEmailAsVerified();

        $after = $this->postJson('/api/v1/email/verification/status', [
            'verification_token' => $challengeToken,
        ]);
        $after->assertStatus(200)
            ->assertJsonPath('data.verified', true);
    }

    public function test_forgot_password_endpoint_returns_generic_success_for_unknown_email()
    {
        $response = $this->postJson('/api/v1/password/forgot', [
            'email' => 'missing-user@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_update_email_resets_verification_and_revokes_tokens()
    {
        Notification::fake();

        $user = User::create([
            'username' => 'account-user',
            'email' => 'account-user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $user->createToken('android');
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/account', [
            'username' => 'account-user',
            'email' => 'new-account-user@example.com',
            'deskripsi' => 'updated profile',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.requires_email_verification', true);

        $updated = $user->fresh();
        $this->assertSame('new-account-user@example.com', $updated->email);
        $this->assertNull($updated->email_verified_at);
        $this->assertCount(0, $updated->tokens()->get());

        Notification::assertSentTo($updated, CustomVerifyEmailNotification::class);
    }
}
