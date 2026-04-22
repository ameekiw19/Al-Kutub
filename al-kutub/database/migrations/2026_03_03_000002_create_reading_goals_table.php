<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingGoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk tracking reading goals user (daily/weekly)
     */
    public function up(): void
    {
        if (Schema::hasTable('reading_goals')) {
            return;
        }

        Schema::create('reading_goals', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('goal_type', ['daily', 'weekly'])->default('daily');
            $table->integer('target_minutes')->default(30); // Target waktu baca (menit)
            $table->integer('target_pages')->default(10); // Target halaman
            $table->integer('current_minutes')->default(0); // Progress waktu saat ini
            $table->integer('current_pages')->default(0); // Progress halaman saat ini
            $table->date('start_date'); // Tanggal mulai (untuk daily = hari ini, weekly = Senin)
            $table->date('end_date')->nullable(); // Tanggal selesai (untuk weekly)
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Index untuk performance
            $table->index(['user_id', 'goal_type', 'start_date']);
            $table->index(['user_id', 'is_completed']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_goals');
    }
}
