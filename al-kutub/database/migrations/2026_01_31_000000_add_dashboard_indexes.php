<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDashboardIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Index untuk user registration analytics
            $table->index(['created_at'], 'users_created_at_index');
            $table->index(['role'], 'users_role_index');
        });

        Schema::table('kitab', function (Blueprint $table) {
            // Index untuk kitab analytics
            $table->index(['views'], 'kitab_views_index');
            $table->index(['downloads'], 'kitab_downloads_index');
            $table->index(['kategori'], 'kitab_kategori_index');
            $table->index(['created_at'], 'kitab_created_at_index');
        });

        Schema::table('history', function (Blueprint $table) {
            // Index untuk history analytics
            $table->index(['user_id'], 'history_user_id_index');
            $table->index(['kitab_id'], 'history_kitab_id_index');
            $table->index(['last_read_at'], 'history_last_read_at_index');
            $table->index(['user_id', 'last_read_at'], 'history_user_date_index');
            $table->index(['kitab_id', 'last_read_at'], 'history_kitab_date_index');
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            // Index untuk bookmark analytics
            $table->index(['user_id'], 'bookmarks_user_id_index');
            $table->index(['id_kitab'], 'bookmarks_kitab_id_index');
            $table->index(['created_at'], 'bookmarks_created_at_index');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            // Index untuk notification analytics
            $table->index(['type'], 'app_notifications_type_index');
            $table->index(['created_at'], 'app_notifications_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_index');
            $table->dropIndex('users_role_index');
        });

        Schema::table('kitab', function (Blueprint $table) {
            $table->dropIndex('kitab_views_index');
            $table->dropIndex('kitab_downloads_index');
            $table->dropIndex('kitab_kategori_index');
            $table->dropIndex('kitab_created_at_index');
        });

        Schema::table('history', function (Blueprint $table) {
            $table->dropIndex('history_user_id_index');
            $table->dropIndex('history_kitab_id_index');
            $table->dropIndex('history_last_read_at_index');
            $table->dropIndex('history_user_date_index');
            $table->dropIndex('history_kitab_date_index');
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropIndex('bookmarks_user_id_index');
            $table->dropIndex('bookmarks_kitab_id_index');
            $table->dropIndex('bookmarks_created_at_index');
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex('app_notifications_type_index');
            $table->dropIndex('app_notifications_created_at_index');
        });
    }
}
