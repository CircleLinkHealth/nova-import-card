<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientCarePlansTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_care_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('patient_care_plans_patient_id_foreign');
            $table->integer('provider_approver_id')->unsigned()->nullable()->index('patient_care_plans_provider_approver_id_foreign');
            $table->integer('qa_approver_id')->unsigned()->nullable()->index('patient_care_plans_qa_approver_id_foreign');
            $table->integer('care_plan_template_id')->unsigned()->index('patient_care_plans_care_plan_template_id_foreign');
            $table->text('type', 65535);
            $table->text('status', 65535);
            $table->dateTime('qa_date')->default('0000-00-00 00:00:00');
            $table->dateTime('provider_date')->default('0000-00-00 00:00:00');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('patient_care_plans');
    }

}
