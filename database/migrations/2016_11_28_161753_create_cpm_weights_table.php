<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmWeightsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_weights', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('cpm_weights_patient_id_foreign');
            $table->string('starting');
            $table->string('target');
            $table->boolean('monitor_changes_for_chf');
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
        Schema::drop('cpm_weights');
    }

}
