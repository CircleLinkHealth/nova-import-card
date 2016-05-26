<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpmProblemsActivateCpmBiometrics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpm_problems_activate_cpm_biometrics', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('care_plan_template_id');
            $table->foreign('care_plan_template_id', 'cpt_problem_activates_biometric_foreign')
                ->references('id')
                ->on('care_plan_templates')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('cpm_problem_id');
            $table->foreign('cpm_problem_id', 'cpm_problem_problem_activates_biometric_foreign')
                ->references('id')
                ->on('cpm_problems')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedInteger('cpm_biometric_id');
            $table->foreign('cpm_biometric_id', 'cpm_biometric_problem_activates_biometric_foreign')
                ->references('id')
                ->on('cpm_biometrics')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['cpm_biometric_id', 'care_plan_template_id', 'cpm_problem_id'],
                'cpt_problem_activates_med_group');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cpm_problems_activate_cpm_biometrics');
    }
}
