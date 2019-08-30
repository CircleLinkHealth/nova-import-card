<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIndianFromQ1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionTypesAnswers = "question_types_answers";
        $qTypeAnswerId = DB::table($questionTypesAnswers)
            ->where('value', 'Indian')
            ->first()->id;

        DB::table($questionTypesAnswers)
            ->delete($qTypeAnswerId);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questionTypes        = "question_types";
        $questionTypesAnswers = "question_types_answers";
        $questionsTable       = "questions";

        $qId = DB::table($questionsTable)
                 ->where('body', 'What is your race?')
                 ->first()->id;

        $qTypeId = DB::table($questionTypes)
                     ->where('question_id', $qId)
                     ->first()->id;

        DB::table($questionTypesAnswers)
          ->insert([
              'question_type_id' => $qTypeId,
              'value' => 'Indian'
          ]);
    }
}
