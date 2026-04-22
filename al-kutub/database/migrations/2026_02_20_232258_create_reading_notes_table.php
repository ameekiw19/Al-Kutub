<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reading_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('kitab_id');
            $table->text('note_content');
            $table->integer('page_number')->nullable();
            $table->text('highlighted_text')->nullable();
            $table->string('note_color', 7)->default('#FFFF00'); // Default yellow
            $table->boolean('is_private')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'kitab_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_notes');
    }
};
