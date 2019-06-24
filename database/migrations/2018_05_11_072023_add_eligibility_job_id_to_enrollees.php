<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEligibilityJobIdToEnrollees extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign(['eligibility_job_id']);
            $table->dropColumn('eligibility_job_id');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('enrollees', 'eligibility_job_id')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->unsignedInteger('eligibility_job_id')
                    ->nullable()
                    ->after('batch_id');

                $table->foreign('eligibility_job_id')
                    ->references('id')
                    ->on('eligibility_jobs')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }
}
