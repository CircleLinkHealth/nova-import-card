<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyProblemForeignKeyOnCallProblemsTable extends Migration
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
        Schema::table('call_problems', function (Blueprint $table) {
            $table->dropForeign(['ccd_problem_id']);

            $table->foreign('ccd_problem_id')
                ->references('id')
                ->on('ccd_problems')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
}
