<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEnrolleesKeys extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->unique(['practice_id', 'mrn']);
            $table->unique(['practice_id', 'first_name', 'last_name', 'dob']);
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('primary_insurance')
                ->nullable()
                ->change();

            $table->string('secondary_insurance')
                ->nullable()
                ->change();

            $table->string('tertiary_insurance')
                ->nullable()
                ->change();

            $table->string('lang')
                ->nullable()
                ->change();

            $table->unsignedInteger('cpm_problem_2')
                ->nullable()
                ->change();

            $table->dropUnique(['practice_id', 'mrn']);
            $table->dropUnique(['practice_id', 'first_name', 'last_name', 'dob']);

            $table->unique('eligibility_job_id');
        });
    }
}
