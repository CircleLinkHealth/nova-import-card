<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Answers');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('survey_instance_id');
            $table->unsignedInteger('question_id');
            $table->unsignedInteger('question_type_answer_id')->nullable();
            $table->json('value');
            $table->timestamps();

            /* $table->foreign('user_id')
                   ->references('id')
                   ->on('users');*/
        });
    }
}
