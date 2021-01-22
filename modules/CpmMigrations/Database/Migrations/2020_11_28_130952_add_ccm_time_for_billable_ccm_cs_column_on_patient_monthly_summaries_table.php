<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCcmTimeForBillableCcmCsColumnOnPatientMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('ccm_time_for_billable_ccm_cs');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->integer('ccm_time_for_billable_ccm_cs')->default(0);
        });
    }
}
