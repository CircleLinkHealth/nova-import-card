<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientMonthlySummariesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign('patient_monthly_summaries_actor_id_foreign');
            $table->dropForeign('patient_monthly_summaries_patient_id_foreign');
            $table->dropForeign('patient_monthly_summaries_problem_1_foreign');
            $table->dropForeign('patient_monthly_summaries_problem_2_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('users')->onUpdate('CASCADE');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE');
            $table->foreign('problem_1')->references('id')->on('ccd_problems')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('problem_2')->references('id')->on('ccd_problems')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
}
