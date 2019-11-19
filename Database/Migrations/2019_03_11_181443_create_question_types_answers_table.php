<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionTypesAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_types_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_type_id');
            $table->string('value')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('question_type_id')
                  ->references('id')->on('question_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_types_answers');
    }
}
