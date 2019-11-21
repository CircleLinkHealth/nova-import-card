<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurveyQuestionsTable extends Migration
{
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_questions');
    }
}
