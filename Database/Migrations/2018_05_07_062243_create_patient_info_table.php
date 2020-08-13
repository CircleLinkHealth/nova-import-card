<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientInfoTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('patient_info');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('imported_medical_record_id')->unsigned()->nullable()->index('patient_info_imported_medical_record_id_foreign');
            $table->integer('user_id')->unsigned()->index('patient_info_user_id_foreign');
            $table->integer('ccda_id')->unsigned()->nullable();
            $table->integer('care_plan_id')->unsigned()->nullable()->index('patient_info_care_plan_id_foreign');
            $table->string('active_date')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_telephone')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('agent_relationship')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('ccm_status')->nullable();
            $table->dateTime('paused_letter_printed_at')->nullable();
            $table->date('consent_date')->nullable();
            $table->integer('cur_month_activity_time')->unsigned()->nullable();
            $table->string('gender')->nullable();
            $table->dateTime('date_paused')->nullable();
            $table->dateTime('date_withdrawn')->nullable();
            $table->dateTime('date_unreachable')->nullable();
            $table->string('mrn_number')->nullable();
            $table->string('preferred_cc_contact_days')->nullable();
            $table->string('preferred_contact_language')->nullable();
            $table->string('preferred_contact_location')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->string('preferred_contact_time')->nullable();
            $table->string('preferred_contact_timezone')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('daily_reminder_optin')->nullable();
            $table->string('daily_reminder_time')->nullable();
            $table->string('daily_reminder_areas')->nullable();
            $table->string('hospital_reminder_optin')->nullable();
            $table->string('hospital_reminder_time')->nullable();
            $table->string('hospital_reminder_areas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->text('general_comment')->nullable();
            $table->integer('preferred_calls_per_month')->default(1);
            $table->dateTime('last_successful_contact_time')->nullable();
            $table->integer('no_call_attempts_since_last_success')->nullable();
            $table->time('daily_contact_window_start')->default('09:00:00');
            $table->time('daily_contact_window_end')->default('18:00:00');
            $table->integer('next_call_id')->unsigned()->nullable()->index('patient_info_next_call_id_foreign');
            $table->integer('family_id')->unsigned()->nullable()->index('patient_info_family_id_foreign');
            $table->dateTime('date_welcomed')->nullable();
        });
    }
}
