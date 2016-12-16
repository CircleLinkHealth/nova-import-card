<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientInfoTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('patient_info_user_id_foreign');
            $table->integer('ccda_id')->unsigned();
            $table->string('active_date')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_telephone')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('agent_relationship')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('ccm_status')->nullable();
            $table->string('consent_date')->nullable();
            $table->string('cur_month_activity_time')->nullable();
            $table->string('gender')->nullable();
            $table->string('date_paused')->nullable();
            $table->string('date_withdrawn')->nullable();
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
            $table->string('careplan_last_printed');
            $table->string('careplan_qa_approver');
            $table->string('careplan_qa_date');
            $table->string('careplan_provider_approver');
            $table->string('careplan_provider_date');
            $table->string('careplan_status');
            $table->timestamps();
            $table->softDeletes();
            $table->text('general_comment', 65535);
            $table->integer('preferred_calls_per_month')->default(2);
            $table->dateTime('last_successful_contact_time');
            $table->integer('no_call_attempts_since_last_success')->nullable();
            $table->dateTime('last_contact_time');
            $table->time('daily_contact_window_start')->default('09:00:00');
            $table->time('daily_contact_window_end')->default('18:00:00');
            $table->integer('next_call_id')->unsigned()->nullable()->index('patient_info_next_call_id_foreign');
            $table->integer('family_id')->unsigned()->nullable()->index('patient_info_family_id_foreign');
            $table->dateTime('date_welcomed')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('patient_info');
    }

}
