<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmProblemsActivateCpmBiometricsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_problems_activate_cpm_biometrics', function (Blueprint $table) {
            $table->dropForeign('cpm_biometric_problem_activates_biometric_foreign');
            $table->dropForeign('cpm_problem_problem_activates_biometric_foreign');
            $table->dropForeign('cpt_problem_activates_biometric_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_problems_activate_cpm_biometrics', function (Blueprint $table) {
            $table->foreign('cpm_biometric_id', 'cpm_biometric_problem_activates_biometric_foreign')->references('id')->on('cpm_biometrics')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_problem_id', 'cpm_problem_problem_activates_biometric_foreign')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('care_plan_template_id', 'cpt_problem_activates_biometric_foreign')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
