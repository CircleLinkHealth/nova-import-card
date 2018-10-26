<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProblemsToEligJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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

            $table->string('ternary_insurance')
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('bhi_problem_id');
            $table->dropColumn('ccm_problem_1_id');
            $table->dropColumn('ccm_problem_2_id');
            $table->dropColumn('primary_insurance');
            $table->dropColumn('secondary_insurance');
            $table->dropColumn('ternary_insurance');
            $table->dropColumn('last_encounter');
        });
    }
}
