<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmBloodSugarsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_blood_sugars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('cpm_blood_sugars_patient_id_foreign');
            $table->string('starting');
            $table->string('target');
            $table->string('starting_a1c');
            $table->string('high_alert');
            $table->string('low_alert');
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
        Schema::drop('cpm_blood_sugars');
    }

}
