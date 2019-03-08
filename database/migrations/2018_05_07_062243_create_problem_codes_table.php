<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProblemCodesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('problem_codes');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('problem_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('problem_code_system_id')->unsigned()->nullable()->index('problem_codes_problem_code_system_id_foreign');
            $table->integer('problem_id')->unsigned()->index('problem_codes_problem_id_foreign');
            $table->string('code_system_name', 20);
            $table->string('code_system_oid', 50)->nullable();
            $table->string('code', 20);
            $table->string('name', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
