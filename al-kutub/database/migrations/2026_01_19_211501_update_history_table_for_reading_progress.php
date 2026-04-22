<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->integer('current_page')->default(0)->after('last_read_at');
            $table->integer('total_pages')->default(0)->after('current_page');
            $table->text('last_position')->nullable()->after('total_pages'); // JSON untuk posisi detail
            $table->integer('reading_time_minutes')->default(0)->after('last_position');
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn(['current_page', 'total_pages', 'last_position', 'reading_time_minutes']);
        });
    }
};
