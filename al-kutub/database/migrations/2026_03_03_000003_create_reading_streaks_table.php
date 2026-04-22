<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadingStreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel untuk tracking reading streaks (hari berturut-turut baca)
     */
    public function up(): void
    {
        if (Schema::hasTable('reading_streaks')) {
            return;
        }

        Schema::create('reading_streaks', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('current_streak')->default(0); // Streak saat ini (hari)
            $table->integer('longest_streak')->default(0); // Streak terpanjang
            $table->date('last_read_date')->nullable(); // Terakhir kali baca
            $table->integer('total_days')->default(0); // Total hari baca (sejak daftar)
            $table->json('streak_history')->nullable(); // History streak per tanggal
            $table->timestamps();

            // Index untuk performance
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_streaks');
    }
}
