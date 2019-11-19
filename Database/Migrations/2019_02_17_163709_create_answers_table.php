<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration
{
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



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Answers');
    }
}
