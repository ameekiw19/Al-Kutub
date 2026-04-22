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
        Schema::create('kitab_revisions', function (Blueprint $table) {
            $table->id();
            $table->integer('kitab_id');
            $table->unsignedInteger('version_no')->default(1);
            $table->string('action', 50);
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->json('changed_fields')->nullable();
            $table->string('old_file_pdf')->nullable();
            $table->string('old_cover')->nullable();
            $table->integer('actor_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['kitab_id', 'version_no'], 'kitab_revision_kitab_version_idx');
            $table->index(['kitab_id', 'created_at'], 'kitab_revision_kitab_created_idx');
            $table->index('action', 'kitab_revision_action_idx');

            $table->foreign('kitab_id')
                ->references('id_kitab')
                ->on('kitab')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitab_revisions');
    }
};
