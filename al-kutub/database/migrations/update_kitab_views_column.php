<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Normalize data first, then enforce NOT NULL + DEFAULT using raw SQL.
        DB::statement('UPDATE kitab SET views = 0 WHERE views IS NULL');
        DB::statement('ALTER TABLE kitab MODIFY views INT(11) NOT NULL DEFAULT 0');
    }

    public function down()
    {
        DB::statement('ALTER TABLE kitab MODIFY views INT(11) NULL DEFAULT NULL');
    }
};
