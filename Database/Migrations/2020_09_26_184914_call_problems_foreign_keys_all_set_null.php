<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CallProblemsForeignKeysAllSetNull extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['ccd_problem_id']);
            $table->dropForeign(['call_id']);
            $table->dropForeign(['patient_user_id']);
            $table->dropForeign(['addendum_id']);
            $table->dropForeign(['patient_monthly_summary_id']);

            $table->unsignedInteger('ccd_problem_id')->nullable(true)->change();

            $table->foreign('call_id')
                ->references('id')
                ->on('calls')
                ->onDelete('set null');

            $table->foreign('ccd_problem_id')
                ->references('id')
                ->on('ccd_problems')
                ->onDelete('set null');

            $table->foreign('patient_monthly_summary_id')
                ->references('id')
                ->on('patient_monthly_summaries')
                ->onDelete('set null');

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('addendum_id')
                ->references('id')
                ->on('addendums')
                ->onDelete('set null');
        });
    }
}
