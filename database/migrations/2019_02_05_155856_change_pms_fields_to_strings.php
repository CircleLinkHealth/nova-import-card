<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePmsFieldsToStrings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
            $table->string('billable_problem1')->nullable()->change();
            $table->string('billable_problem1_code')->nullable()->change();
            $table->string('billable_problem2')->nullable()->change();
            $table->string('billable_problem2_code')->nullable()->change();
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
            //
        });
    }
}
