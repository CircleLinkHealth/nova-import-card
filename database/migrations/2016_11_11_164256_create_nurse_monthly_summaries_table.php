<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseMonthlySummariesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_monthly_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_id')->unsigned()->index('nurse_monthly_summaries_nurse_id_foreign');
            $table->date('month_year');
            $table->integer('time')->nullable();
            $table->integer('ccm_time')->nullable();
            $table->integer('no_of_calls')->nullable();
            $table->integer('no_of_successful_calls')->nullable();
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
        Schema::drop('nurse_monthly_summaries');
    }

}
