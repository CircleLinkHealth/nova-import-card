<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmProblemsUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_problems_users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_problems_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('cpm_problems_users_cpm_instruction_id_foreign');
            $table->integer('patient_id')->unsigned();
            $table->integer('cpm_problem_id')->unsigned()->index('cpm_problems_users_cpm_problem_id_foreign');
            $table->timestamps();
            $table->index(['patient_id', 'cpm_problem_id']);
        });
    }
}
