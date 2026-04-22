<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kitab', function (Blueprint $table) {
            if (!Schema::hasColumn('kitab', 'publication_status')) {
                $table->string('publication_status', 20)->default('draft')->after('viewed_by');
            }
            if (!Schema::hasColumn('kitab', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('publication_status');
            }
            if (!Schema::hasColumn('kitab', 'reviewed_by')) {
                $table->integer('reviewed_by')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('kitab', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('kitab', 'published_by')) {
                $table->integer('published_by')->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('kitab', 'status_note')) {
                $table->text('status_note')->nullable()->after('published_by');
            }
        });

        // Existing records are considered already published to preserve current behavior.
        DB::table('kitab')->update([
            'publication_status' => 'published',
            'published_at' => now(),
        ]);

        Schema::table('kitab', function (Blueprint $table) {
            $table->index('publication_status', 'kitab_publication_status_index');
            $table->index('published_at', 'kitab_published_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitab', function (Blueprint $table) {
            if (Schema::hasColumn('kitab', 'publication_status')) {
                $table->dropIndex('kitab_publication_status_index');
            }
            if (Schema::hasColumn('kitab', 'published_at')) {
                $table->dropIndex('kitab_published_at_index');
            }

            $columns = [];
            foreach ([
                'publication_status',
                'reviewed_at',
                'reviewed_by',
                'published_at',
                'published_by',
                'status_note',
            ] as $column) {
                if (Schema::hasColumn('kitab', $column)) {
                    $columns[] = $column;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
