<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingColumnsToHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            if (!Schema::hasColumn('history', 'current_page')) {
                $table->integer('current_page')->nullable()->after('last_read_at');
            }
            if (!Schema::hasColumn('history', 'total_pages')) {
                $table->integer('total_pages')->nullable()->after('current_page');
            }
            if (!Schema::hasColumn('history', 'last_position')) {
                $table->text('last_position')->nullable()->after('total_pages');
            }
            if (!Schema::hasColumn('history', 'reading_time_minutes')) {
                $table->integer('reading_time_minutes')->default(0)->after('last_position');
            }
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('history', 'current_page')) $columnsToDrop[] = 'current_page';
            if (Schema::hasColumn('history', 'total_pages')) $columnsToDrop[] = 'total_pages';
            if (Schema::hasColumn('history', 'last_position')) $columnsToDrop[] = 'last_position';
            if (Schema::hasColumn('history', 'reading_time_minutes')) $columnsToDrop[] = 'reading_time_minutes';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
}
