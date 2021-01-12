<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBhiTimeToPatientMonthlySummary extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('bhi_time');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('patient_monthly_summaries', 'bhi_time')) {
            Schema::table('patient_monthly_summaries', function (Blueprint $table) {
                $table->integer('bhi_time')
                    ->after('ccm_time')
                    ->default(0)
                    ->nullable();
            });
        }
    }
}
