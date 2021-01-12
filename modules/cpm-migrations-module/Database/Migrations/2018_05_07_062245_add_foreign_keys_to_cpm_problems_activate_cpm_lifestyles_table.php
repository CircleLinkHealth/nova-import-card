<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmProblemsActivateCpmLifestylesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_problems_activate_cpm_lifestyles', function (Blueprint $table) {
            $table->dropForeign('cpm_lifestyle_problem_activates_lifestyle_foreign');
            $table->dropForeign('cpm_problem_problem_activates_lifestyle_foreign');
            $table->dropForeign('cpt_problem_activates_lifestyle_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_problems_activate_cpm_lifestyles', function (Blueprint $table) {
            $table->foreign('cpm_lifestyle_id', 'cpm_lifestyle_problem_activates_lifestyle_foreign')->references('id')->on('cpm_lifestyles')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_problem_id', 'cpm_problem_problem_activates_lifestyle_foreign')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('care_plan_template_id', 'cpt_problem_activates_lifestyle_foreign')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
