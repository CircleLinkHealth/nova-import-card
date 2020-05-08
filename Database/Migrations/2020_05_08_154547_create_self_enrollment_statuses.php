<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelfEnrollmentStatuses extends Migration
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
        Schema::create('self_enrollment_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enrollee_id');
            $table->unsignedInteger('enrollee_user_id')->nullable();
            $table->unsignedInteger('enrollee_patient_info')->nullable();
            $table->string('awv_survey_status')->nullable();
            $table->boolean('logged_in')->default(false);
            $table->timestamps();
        });
    }
}
