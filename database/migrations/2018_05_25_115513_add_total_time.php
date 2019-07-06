<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalTime extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn('total_time');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('patient_monthly_summaries', 'total_time')) {
            Schema::table('patient_monthly_summaries', function (Blueprint $table) {
                $table->integer('total_time')
                    ->nullable()
                    ->default(0)
                    ->after('patient_id');
            });
        }
    }
}
