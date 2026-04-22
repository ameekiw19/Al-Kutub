<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_notifications')) {
            return;
        }

        Schema::table('app_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('app_notifications', 'data')) {
                // JSON-like metadata for notification payload (kitab_id, action params, etc.)
                $table->longText('data')->nullable()->after('action_url');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_notifications')) {
            return;
        }

        Schema::table('app_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('app_notifications', 'data')) {
                $table->dropColumn('data');
            }
        });
    }
};

