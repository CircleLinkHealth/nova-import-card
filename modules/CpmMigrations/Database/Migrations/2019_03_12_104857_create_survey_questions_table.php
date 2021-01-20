<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyQuestionsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_questions');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('survey_instance_id');
            $table->integer('question_id');
            $table->integer('order');
            $table->string('sub_order')->nullable();
            $table->timestamps();

            $table->unique(['survey_instance_id', 'order', 'sub_order'], 'survey_instance_order_unique');
        });
    }
}
