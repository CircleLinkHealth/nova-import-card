<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingRevampColumnsOnCallProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['patient_user_id']);

            $table->dropColumn([
                'patient_user_id',
                'chargeable_month',
                'ccd_problem_name',
                'ccd_problem_icd_10_code',
            ]);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_problems', function (Blueprint $table) {
            $table->date('chargeable_month')->nullable()->after('id');
            $table->string('ccd_problem_name')->nullable()->after('ccd_problem_id');
            $table->string('ccd_problem_icd_10_code')->nullable()->after('ccd_problem_name');
            $table->unsignedInteger('patient_user_id')->nullable()->after('id');

            $table->foreign('patient_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
}
