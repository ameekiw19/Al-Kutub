<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kitab_transcript_segments')) {
            return;
        }

        Schema::table('kitab_transcript_segments', function (Blueprint $table) {
            if (!Schema::hasColumn('kitab_transcript_segments', 'content_translation')) {
                $table->longText('content_translation')->nullable()->after('content');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'content_arabic')) {
                $table->longText('content_arabic')->nullable()->after('content_translation');
            }
        });

        if (Schema::hasColumn('kitab_transcript_segments', 'content') && Schema::hasColumn('kitab_transcript_segments', 'content_translation')) {
            \DB::table('kitab_transcript_segments')
                ->whereNull('content_translation')
                ->update([
                    'content_translation' => \DB::raw('content'),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('kitab_transcript_segments')) {
            return;
        }

        Schema::table('kitab_transcript_segments', function (Blueprint $table) {
            $columns = [
                'content_translation',
                'content_arabic',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('kitab_transcript_segments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
