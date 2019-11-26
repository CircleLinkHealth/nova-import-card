<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEligibilityInfoColumnToTargetPatientsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->dropColumn('eligibility_job_id');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('target_patients', 'eligibility_job_id')) {
            return;
        }

        Schema::table('target_patients', function (Blueprint $table) {
            $table->text('eligibility_job_id')->nullable()->after('batch_id');
        });
    }
}
