<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientMonthlySummariesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_monthly_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_info_id')->unsigned()->index('patient_monthly_summaries_patient_info_id_foreign');
            $table->integer('ccm_time');
            $table->date('month_year');
            $table->integer('no_of_calls');
            $table->integer('no_of_successful_calls');
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
        Schema::drop('patient_monthly_summaries');
    }

}
