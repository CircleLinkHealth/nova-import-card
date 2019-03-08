<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProblemCodesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('problem_codes', function (Blueprint $table) {
            $table->dropForeign('problem_codes_problem_code_system_id_foreign');
            $table->dropForeign('problem_codes_problem_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('problem_codes', function (Blueprint $table) {
            $table->foreign('problem_code_system_id')->references('id')->on('problem_code_systems')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('problem_id')->references('id')->on('ccd_problems')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
