<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reading_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('reading_notes', 'client_request_id')) {
                $table->string('client_request_id', 64)->nullable()->after('is_private');
            }

            $table->unique(
                ['user_id', 'client_request_id'],
                'reading_notes_user_client_request_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reading_notes', function (Blueprint $table) {
            $table->dropUnique('reading_notes_user_client_request_unique');
            if (Schema::hasColumn('reading_notes', 'client_request_id')) {
                $table->dropColumn('client_request_id');
            }
        });
    }
};
