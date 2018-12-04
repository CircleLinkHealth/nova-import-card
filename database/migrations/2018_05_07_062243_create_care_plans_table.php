<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlansTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('care_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('mode', array('web','pdf'))->default('web');
            $table->integer('user_id')->unsigned()->index('patient_care_plans_patient_id_foreign');
            $table->integer('provider_approver_id')->unsigned()->nullable()->index('patient_care_plans_provider_approver_id_foreign');
            $table->integer('qa_approver_id')->unsigned()->nullable()->index('patient_care_plans_qa_approver_id_foreign');
            $table->integer('care_plan_template_id')->unsigned()->index('patient_care_plans_care_plan_template_id_foreign');
            $table->text('type', 65535)->nullable();
            $table->text('status', 65535)->nullable();
            $table->dateTime('qa_date')->nullable();
            $table->dateTime('provider_date')->nullable();
            $table->integer('first_printed_by')->unsigned()->nullable()->index('care_plans_first_printed_by_foreign');
            $table->dateTime('first_printed')->nullable();
            $table->string('last_printed')->nullable();
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
        Schema::drop('care_plans');
    }
}
