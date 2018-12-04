<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlansTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->dropForeign('care_plans_first_printed_by_foreign');
            $table->dropForeign('patient_care_plans_care_plan_template_id_foreign');
            $table->dropForeign('patient_care_plans_patient_id_foreign');
            $table->dropForeign('patient_care_plans_provider_approver_id_foreign');
            $table->dropForeign('patient_care_plans_qa_approver_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->foreign('first_printed_by')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('care_plan_template_id', 'patient_care_plans_care_plan_template_id_foreign')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'patient_care_plans_patient_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('provider_approver_id', 'patient_care_plans_provider_approver_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('qa_approver_id', 'patient_care_plans_qa_approver_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
