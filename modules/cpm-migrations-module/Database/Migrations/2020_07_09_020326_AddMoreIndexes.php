<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndexes extends Migration
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
    {
        //for PracticeReportable@callCount
        Schema::table('calls', function (Blueprint $table) {
            $table->index([
                'type',
                'sub_type',
                'called_date',
            ]);
        });

        //for PracticeReportable@totalBilledPatientsCount
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->index([
                'patient_id',
                'total_time',
            ]);
        });

        //for TotalTimeAggregator@aggregate
        Schema::table('lv_page_timer', function (Blueprint $table) {
            $table->index([
                'provider_id',
                'start_time',
            ]);
        });

        Schema::table('lv_activities', function (Blueprint $table) {
            $table->index([
                'patient_id',
                'performed_at',
                'logged_from',
            ]);
        });

        Schema::table('nurse_invoice_daily_disputes', function (Blueprint $table) {
            $table->index([
                'status',
            ]);
        });
    }
}
