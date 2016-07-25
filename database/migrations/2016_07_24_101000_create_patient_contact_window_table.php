<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientContactWindowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_contact_window', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_info_id');
            $table->integer('day_of_week');
            $table->time('window_time_start');
            $table->time('window_time_end');
            $table->timestamps();
            $table->foreign('patient_info_id')
                ->references('id')
                ->on('patient_info')
                ->onUpdate('cascade')
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
        Schema::table('patient_contact_window', function (Blueprint $table) {

            $table->drop();

        });
    }
}
