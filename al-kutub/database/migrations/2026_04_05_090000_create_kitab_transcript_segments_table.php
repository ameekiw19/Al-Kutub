<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kitab_transcript_segments')) {
            return;
        }

        Schema::create('kitab_transcript_segments', function (Blueprint $table) {
            $table->id();
            $table->integer('kitab_id');
            $table->string('section_key', 120)->nullable();
            $table->string('transcript_type', 40)->default('page');
            $table->string('title')->nullable();
            $table->longText('content');
            $table->longText('content_translation')->nullable();
            $table->longText('content_arabic')->nullable();
            $table->string('language', 32)->nullable();
            $table->unsignedInteger('page_start')->nullable();
            $table->unsignedInteger('page_end')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('kitab_id')
                ->references('id_kitab')
                ->on('kitab')
                ->cascadeOnDelete();

            $table->index(['kitab_id', 'is_active', 'sort_order'], 'kitab_transcript_lookup_idx');
            $table->index(['kitab_id', 'page_start', 'page_end'], 'kitab_transcript_page_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitab_transcript_segments');
    }
};
