<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            // Tambah kolom untuk tracking progress baca hanya jika belum ada
            if (!Schema::hasColumn('history', 'current_page')) {
                $table->integer('current_page')->default(0)->after('last_read_at');
            }
            if (!Schema::hasColumn('history', 'total_pages')) {
                $table->integer('total_pages')->default(0)->after('current_page');
            }
            if (!Schema::hasColumn('history', 'last_position')) {
                $table->text('last_position')->nullable()->after('total_pages'); // JSON untuk posisi detail
            }
            if (!Schema::hasColumn('history', 'reading_time_minutes')) {
                $table->integer('reading_time_minutes')->default(0)->after('last_position');
            }
        });
        
        // Update semua record yang NULL menjadi 0
        if (Schema::hasColumn('history', 'current_page')) {
            \DB::statement('UPDATE history SET current_page = 0 WHERE current_page IS NULL');
        }
        if (Schema::hasColumn('history', 'total_pages')) {
            \DB::statement('UPDATE history SET total_pages = 0 WHERE total_pages IS NULL');
        }
        if (Schema::hasColumn('history', 'reading_time_minutes')) {
            \DB::statement('UPDATE history SET reading_time_minutes = 0 WHERE reading_time_minutes IS NULL');
        }
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn(['current_page', 'total_pages', 'last_position', 'reading_time_minutes']);
        });
    }
};