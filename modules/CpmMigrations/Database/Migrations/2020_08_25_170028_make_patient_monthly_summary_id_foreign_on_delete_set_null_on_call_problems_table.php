<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePatientMonthlySummaryIdForeignOnDeleteSetNullOnCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['patient_monthly_summary_id']);

            $table->foreign('patient_monthly_summary_id')
                ->references('id')
                ->on('patient_monthly_summaries');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['patient_monthly_summary_id']);

            $table->foreign('patient_monthly_summary_id')
                ->references('id')
                ->on('patient_monthly_summaries')
                ->onDelete('set null');
        });
    }
}
