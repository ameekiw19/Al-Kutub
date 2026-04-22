<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('id_kitab');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->timestamps();

            // One rating per user per kitab
            $table->unique(['user_id', 'id_kitab']);
            $table->index('id_kitab');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
