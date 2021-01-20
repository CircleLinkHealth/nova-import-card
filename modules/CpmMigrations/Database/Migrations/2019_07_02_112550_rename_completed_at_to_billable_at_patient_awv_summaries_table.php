<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCompletedAtToBillableAtPatientAwvSummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'billable_at')) {
                $table->renameColumn('billable_at', 'completed_at');
            }
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_awv_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('patient_awv_summaries', 'completed_at')) {
                $table->renameColumn('completed_at', 'billable_at');
            }
        });
    }
}
