<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientSessionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('patient_sessions_patient_id_foreign');
            $table->integer('user_id')->unsigned()->index('patient_sessions_user_id_foreign');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('patient_sessions');
    }

}
