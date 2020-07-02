<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePatientMonthlySummariesApprovedColumnToBooleanType extends Migration
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
        if (Schema::hasColumn('patient_monthly_summaries', 'approved')) {
            Schema::table('patient_monthly_summaries', function (Blueprint $table) {
                $table->boolean('approved')->nullable(false)->default(0)->change();
            });
        }
    }
}
