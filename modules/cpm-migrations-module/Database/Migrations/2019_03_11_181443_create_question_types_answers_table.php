<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionTypesAnswersTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_types_answers');
    }

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
}
