<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsCcdProblemsToSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->unsignedInteger('problem_2')
                  ->after('no_of_successful_calls')
                  ->nullable();

            $table->unsignedInteger('problem_1')
                ->after('no_of_successful_calls')
                ->nullable();

            $table->foreign('problem_1')
                ->references('id')
                ->on('ccd_problems')
                ->onUpdate('cascade');

            $table->foreign('problem_2')
                  ->references('id')
                  ->on('ccd_problems')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->dropForeign(['problem_1']);
            $table->dropForeign(['problem_2']);

            $table->dropColumn('problem_1');
            $table->dropColumn('problem_2');
        });
    }
}
