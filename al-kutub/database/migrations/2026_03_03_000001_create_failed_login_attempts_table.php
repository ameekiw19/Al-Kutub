<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedLoginAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Table untuk tracking failed login attempts guna security monitoring
     * dan auto-blocking IP yang mencurigakan.
     */
    public function up(): void
    {
        if (Schema::hasTable('failed_login_attempts')) {
            return;
        }

        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // IPv6 compatible
            $table->string('username')->nullable(); // Username yang dicoba
            $table->string('user_agent')->nullable(); // Browser/device info
            $table->string('reason')->default('invalid_credentials'); // Alasan gagal
            $table->timestamp('created_at')->useCurrent();

            // Index untuk performance query
            $table->index('ip_address');
            $table->index('created_at');
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
}
