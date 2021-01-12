<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSurveysTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_surveys');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_surveys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('survey_instance_id');
            $table->integer('survey_id');
            $table->string('status');
            $table->integer('last_question_answered_id');
            $table->timestamps();
        });
    }
}
