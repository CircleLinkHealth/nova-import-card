<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTargetPatientsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('target_patients');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('target_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('eligibility_job_id')->nullable();
            $table->integer('batch_id')->unsigned()->nullable()->index('target_patients_batch_id_foreign');
            $table->integer('ehr_id')->unsigned()->index('target_patients_ehr_id_foreign');
            $table->integer('user_id')->unsigned()->nullable()->index('target_patients_user_id_foreign');
            $table->integer('enrollee_id')->unsigned()->nullable()->index('target_patients_enrollee_id_foreign');
            $table->integer('ehr_patient_id')->unsigned();
            $table->integer('ehr_practice_id')->unsigned();
            $table->integer('ehr_department_id')->unsigned();
            $table->integer('practice_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->enum('status', ['to_process', 'eligible', 'ineligible', 'consented', 'enrolled', 'error', 'duplicate'])->nullable();
            $table->timestamps();
            $table->string('description');
            $table->foreign('eligibility_job_id')->references('id')->on('eligibility_jobs')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
