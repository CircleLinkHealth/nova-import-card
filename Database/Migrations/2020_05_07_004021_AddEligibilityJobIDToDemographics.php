<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEligibilityJobIDToDemographics extends Migration
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
        if ( ! Schema::hasColumn('practice_pull_demographics', 'eligibility_job_id')) {
            Schema::table('practice_pull_demographics', function (Blueprint $table) {
                $table->unsignedInteger('eligibility_job_id')->nullable();
                $table->foreign('eligibility_job_id')->references('id')->on('eligibility_jobs')->onDelete('set null')->onUpdate('set null');
            });
        }
    }
}
