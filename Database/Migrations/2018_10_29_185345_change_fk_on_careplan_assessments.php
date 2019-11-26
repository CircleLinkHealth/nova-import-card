<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFkOnCareplanAssessments extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->foreign('careplan_id')
                ->references('id')
                ->on('users')
                ->odDelete('cascade')
                ->onUpdate('cascade');
        });
    }
}
