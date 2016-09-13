<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNurseWindowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            if (!Schema::hasTable('nurse_contact_window')) {

                Schema::create('nurse_contact_window', function (Blueprint $table) {
                    $table->increments('id');

                    $table->unsignedInteger('nurse_info_id');

                    $table->integer('day_of_week');

                    $table->time('window_time_start');
                    $table->time('window_time_end');

                    $table->timestamps();
                    $table->foreign('nurse_info_id')

                        ->references('id')
                        ->on('nurse_info')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nurse_contact_window', function (Blueprint $table) {
            //
        });
    }
}
