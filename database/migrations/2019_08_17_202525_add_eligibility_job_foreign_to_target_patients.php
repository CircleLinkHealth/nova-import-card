<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEligibilityJobForeignToTargetPatients extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('target_patients', function (Blueprint $table) {
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
            //Changing column eligibility_job_id type from text to int using the laravel way was failing locally, possibly due to the fact this table has an enum type column. Not sure though.
            //the error message was "Unknown database type enum requested, Doctrine\DBAL\Platforms\MySQL57Platform may not support it. "
            DB::statement('ALTER TABLE target_patients MODIFY COLUMN eligibility_job_id Int(10) UNSIGNED NULL;');
        });

        Schema::table('target_patients', function (Blueprint $table) {
            $table->foreign('eligibility_job_id')->references('id')->on('eligibility_jobs')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
