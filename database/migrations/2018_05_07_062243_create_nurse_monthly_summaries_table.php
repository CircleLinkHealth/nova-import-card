<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('nurse_monthly_summaries');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_monthly_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_id')->unsigned()->index('nurse_monthly_summaries_nurse_id_foreign');
            $table->date('month_year');
            $table->integer('accrued_after_ccm')->default(0);
            $table->integer('accrued_towards_ccm')->default(0);
            $table->integer('no_of_calls')->nullable();
            $table->integer('no_of_successful_calls')->nullable();
            $table->timestamps();
        });
    }
}
