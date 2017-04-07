<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillableProblemsToPatientMonthlySummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {

            $table->text('billable_problem1')->after('no_of_successful_calls');
            $table->text('billable_problem2')->after('billable_problem1');

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

            $table->dropColumn('billable_problem1');
            $table->dropColumn('billable_problem2');

        });
    }
}
