<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('refresh_tokens')) {
            return;
        }

        Schema::table('refresh_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('refresh_tokens', 'device_id')) {
                $table->string('device_id', 120)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('refresh_tokens', 'device_name')) {
                $table->string('device_name', 120)->nullable()->after('device_id');
            }
            if (!Schema::hasColumn('refresh_tokens', 'user_agent')) {
                $table->string('user_agent', 255)->nullable()->after('device_name');
            }
            if (!Schema::hasColumn('refresh_tokens', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('refresh_tokens', 'access_token_id')) {
                $table->unsignedBigInteger('access_token_id')->nullable()->after('token_hash');
            }
            if (!Schema::hasColumn('refresh_tokens', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('last_used_at');
            }
            if (!Schema::hasColumn('refresh_tokens', 'revoked_reason')) {
                $table->string('revoked_reason', 64)->nullable()->after('revoked_at');
            }
            if (!Schema::hasColumn('refresh_tokens', 'replaced_by_token_id')) {
                $table->unsignedBigInteger('replaced_by_token_id')->nullable()->after('revoked_reason');
            }
        });

        Schema::table('refresh_tokens', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'revoked_at', 'expires_at'], 'refresh_tokens_active_lookup_index');
            } catch (\Throwable $e) {
                // index may already exist on some environments
            }
            try {
                $table->index(['device_id'], 'refresh_tokens_device_id_index');
            } catch (\Throwable $e) {
                // index may already exist on some environments
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('refresh_tokens')) {
            return;
        }

        Schema::table('refresh_tokens', function (Blueprint $table) {
            try {
                $table->dropIndex('refresh_tokens_active_lookup_index');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('refresh_tokens_device_id_index');
            } catch (\Throwable $e) {
            }
        });

        Schema::table('refresh_tokens', function (Blueprint $table) {
            $columns = [
                'device_id',
                'device_name',
                'user_agent',
                'ip_address',
                'access_token_id',
                'last_seen_at',
                'revoked_reason',
                'replaced_by_token_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('refresh_tokens', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Throwable $e) {
                    }
                }
            }
        });
    }
};
