<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $afterColumn = Schema::hasColumn('users', 'email_verified_at')
            ? 'email_verified_at'
            : (Schema::hasColumn('users', 'theme_preference') ? 'theme_preference' : 'email');

        Schema::table('users', function (Blueprint $table) use ($afterColumn) {
            if (!Schema::hasColumn('users', 'is_verified_by_admin')) {
                $table->boolean('is_verified_by_admin')->default(false)->after($afterColumn);
            }
            if (!Schema::hasColumn('users', 'admin_verified_at')) {
                $table->timestamp('admin_verified_at')->nullable()->after('is_verified_by_admin');
            }
            if (!Schema::hasColumn('users', 'admin_verified_by')) {
                $table->unsignedBigInteger('admin_verified_by')->nullable()->after('admin_verified_at');
            }
        });

        // Safe rollout:
        // - existing admin accounts are always approved
        // - existing users that already had verified email remain approved
        DB::table('users')
            ->where('role', 'admin')
            ->update([
                'is_verified_by_admin' => true,
                'admin_verified_at' => now(),
            ]);

        DB::table('users')
            ->where('role', '!=', 'admin')
            ->whereNotNull('email_verified_at')
            ->update([
                'is_verified_by_admin' => true,
                'admin_verified_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'admin_verified_by')) {
                $table->dropColumn('admin_verified_by');
            }
            if (Schema::hasColumn('users', 'admin_verified_at')) {
                $table->dropColumn('admin_verified_at');
            }
            if (Schema::hasColumn('users', 'is_verified_by_admin')) {
                $table->dropColumn('is_verified_by_admin');
            }
        });
    }
};
