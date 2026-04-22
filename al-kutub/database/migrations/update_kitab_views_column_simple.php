<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update semua record yang NULL menjadi 0 untuk existing data
        DB::statement('UPDATE kitab SET views = 0 WHERE views IS NULL');
        
        // Kolom views tetap nullable agar admin tidak harus input
        // Tapi kita sudah pastikan tidak ada data NULL yang tersisa
    }

    public function down()
    {
        // Tidak perlu rollback karena ini hanya data cleanup
    }
};
