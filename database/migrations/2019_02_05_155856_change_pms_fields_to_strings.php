<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePmsFieldsToStrings extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_monthly_summaries', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
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
}
