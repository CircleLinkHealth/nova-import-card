<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->dropForeign('ccd_problems_patient_id_foreign');
            $table->dropForeign('ccd_problems_problem_import_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('problem_import_id')->references('id')->on('problem_imports')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
}
