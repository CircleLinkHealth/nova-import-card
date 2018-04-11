<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodesToMonthlyBilling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {

            $table->text('billable_problem1_code')->after('billable_problem1');
            $table->text('billable_problem2_code')->after('billable_problem2');
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

            $table->dropColumn('billable_problem1_code');
            $table->dropColumn('billable_problem2_code');
        });
    }
}
