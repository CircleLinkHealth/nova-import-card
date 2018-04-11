<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientCarePlansTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_care_plans', function (Blueprint $table) {
            $table->foreign('care_plan_template_id')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('provider_approver_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('qa_approver_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_care_plans', function (Blueprint $table) {
            $table->dropForeign('patient_care_plans_care_plan_template_id_foreign');
            $table->dropForeign('patient_care_plans_patient_id_foreign');
            $table->dropForeign('patient_care_plans_provider_approver_id_foreign');
            $table->dropForeign('patient_care_plans_qa_approver_id_foreign');
        });
    }
}
