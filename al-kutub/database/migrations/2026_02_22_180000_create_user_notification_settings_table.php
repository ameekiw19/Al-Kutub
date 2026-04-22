<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unique();
            $table->boolean('enable_notifications')->default(true);
            $table->boolean('new_book_notifications')->default(true);
            $table->boolean('update_notifications')->default(true);
            $table->boolean('reminder_notifications')->default(true);
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_start', 5)->default('22:00');
            $table->string('quiet_hours_end', 5)->default('08:00');
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);
            $table->boolean('led_enabled')->default(true);
            $table->string('notification_style', 20)->default('BASIC');
            $table->json('categories')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notification_settings');
    }
}
