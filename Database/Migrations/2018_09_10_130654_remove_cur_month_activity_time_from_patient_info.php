<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCurMonthActivityTimeFromPatientInfo extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->unsignedInteger('cur_month_activity_time')
                ->default(0);
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('cur_month_activity_time');
        });
    }
}
