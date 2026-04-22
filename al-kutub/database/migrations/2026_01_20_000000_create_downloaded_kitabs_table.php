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
        Schema::create('downloaded_kitabs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id'); // users.id is int(11) signed based on DESCRIBE output
            $table->integer('id_kitab'); // kitab.id_kitab is int(11) signed
            $table->string('file_path');
            $table->bigInteger('file_size')->default(0);
            $table->timestamp('downloaded_at')->useCurrent();
            $table->timestamp('last_accessed_at')->nullable();
            $table->boolean('is_cached')->default(true);
            $table->json('device_info')->nullable();
            $table->timestamps();

            // Foreign keys (assuming users.id and kitab.id_kitab exist)
            // Note: Adjust 'users' and 'kitab' table names if they differ in your DB
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_kitab')->references('id_kitab')->on('kitab')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloaded_kitabs');
    }
};
