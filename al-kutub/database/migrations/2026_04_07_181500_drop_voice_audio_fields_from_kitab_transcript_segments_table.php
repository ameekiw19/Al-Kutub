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
            $columns = [
                'audio_url',
                'audio_translation_url',
                'audio_arabic_url',
                'audio_translation_duration_sec',
                'audio_arabic_duration_sec',
                'audio_translation_source',
                'audio_arabic_source',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('kitab_transcript_segments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('kitab_transcript_segments')) {
            return;
        }

        Schema::table('kitab_transcript_segments', function (Blueprint $table) {
            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_url')) {
                $table->string('audio_url')->nullable()->after('sort_order');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_translation_url')) {
                $table->string('audio_translation_url')->nullable()->after('audio_url');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_arabic_url')) {
                $table->string('audio_arabic_url')->nullable()->after('audio_translation_url');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_translation_duration_sec')) {
                $table->unsignedInteger('audio_translation_duration_sec')->nullable()->after('audio_arabic_url');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_arabic_duration_sec')) {
                $table->unsignedInteger('audio_arabic_duration_sec')->nullable()->after('audio_translation_duration_sec');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_translation_source')) {
                $table->string('audio_translation_source', 32)->nullable()->after('audio_arabic_duration_sec');
            }

            if (!Schema::hasColumn('kitab_transcript_segments', 'audio_arabic_source')) {
                $table->string('audio_arabic_source', 32)->nullable()->after('audio_translation_source');
            }
        });
    }
};
