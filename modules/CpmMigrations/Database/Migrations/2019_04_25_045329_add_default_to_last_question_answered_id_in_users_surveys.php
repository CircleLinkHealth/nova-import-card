<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToLastQuestionAnsweredIdInUsersSurveys extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_surveys', function (Blueprint $table) {
            $table->integer('last_question_answered_id')->default(null)->change();
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_surveys', function (Blueprint $table) {
            $table->integer('last_question_answered_id')->nullable()->change();
        });
    }
}
