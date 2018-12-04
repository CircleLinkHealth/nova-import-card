<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProblemsToEligJobs extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            foreach (
                [
                    'bhi_problem_id',
                    'ccm_problem_2_id',
                    'ccm_problem_1_id',
                ] as $key
            ) {
                $table->dropForeign([$key]);
            }

            $table->dropColumn('bhi_problem_id');
            $table->dropColumn('ccm_problem_1_id');
            $table->dropColumn('ccm_problem_2_id');
            $table->dropColumn('primary_insurance');
            $table->dropColumn('secondary_insurance');
            $table->dropColumn('tertiary_insurance');
            $table->dropColumn('last_encounter');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->unsignedInteger('bhi_problem_id')
                ->nullable()
                ->after('messages');

            $table->unsignedInteger('ccm_problem_2_id')
                ->nullable()
                ->after('messages');

            $table->unsignedInteger('ccm_problem_1_id')
                ->nullable()
                ->after('messages');

            $table->string('tertiary_insurance')
                ->nullable()
                ->after('messages');

            $table->string('secondary_insurance')
                ->nullable()
                ->after('messages');

            $table->string('primary_insurance')
                ->nullable()
                ->after('messages');

            $table->date('last_encounter')
                ->nullable()
                ->after('messages');

            foreach (
                [
                    'bhi_problem_id',
                    'ccm_problem_2_id',
                    'ccm_problem_1_id',
                ] as $key
            ) {
                $table->foreign($key)
                    ->references('id')
                    ->on('cpm_problems')
                    ->onUpdate('cascade');
            }
        });
    }
}
