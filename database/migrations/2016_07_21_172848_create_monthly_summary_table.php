<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlySummaryTable extends Migration
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

            $table->unsignedInteger('patient_info_id');

            $table->integer('ccm_time');

            $table->date('month_year');

            $table->integer('no_of_calls');

            $table->integer('no_of_successful_calls');

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
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {

            $table->drop();

        });
    }
}
