<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexes extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   //for NursePerformanceCalculations@getTotalMonthSystemTimeSeconds
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->index([
                'provider_id',
                'start_time',
                'deleted_at',
            ]);
        });

        //for VariablePayCalculator@getForNurses
        Schema::table('nurse_care_rate_logs', function (Blueprint $table) {
            $table->index([
                'nurse_id',
                'created_at',
                'performed_at',
            ]);
        });

        Schema::table('nurse_info', function (Blueprint $table) {
            $table->index([
                'start_date',
            ]);
        });
    }
}
