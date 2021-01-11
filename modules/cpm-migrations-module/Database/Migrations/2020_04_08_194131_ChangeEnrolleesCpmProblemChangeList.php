<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEnrolleesCpmProblemChangeList extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->dropForeign('enrollees_cpm_problem_1_foreign');
            $table->dropForeign('enrollees_cpm_problem_2_foreign');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('enrollees', function (Blueprint $table) {
            $table->foreign('cpm_problem_1')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('cpm_problem_2')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('SET NULL');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
