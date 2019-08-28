<?php

use Illuminate\Database\Migrations\Migration;

class ModifyQ11cToAllowCustomInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionTypes        = "question_types";
        $questionTypesAnswers = "question_types_answers";
        $questionsTable       = "questions";

        $qId = DB::table($questionsTable)
                 ->where('body', 'On average, how many packs/day do or did you smoke?')
                 ->first()->id;

        $qTypeId = DB::table($questionTypes)
                     ->where('question_id', $qId)
                     ->first()->id;

        DB::table($questionTypesAnswers)
          ->where('question_type_id', $qTypeId)
          ->where('value', 'Other')
          ->update([
              'value'   => null,
              'options' => json_encode([
                  'placeholder'               => 'Other',
                  'answer_type'               => 'text',
                  'allow_single_custom_input' => true,
              ]),
          ]);
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
                 ->where('body', 'On average, how many packs/day do or did you smoke?')
                 ->first()->id;

        $qTypeId = DB::table($questionTypes)
                     ->where('question_id', $qId)
                     ->first()->id;

        DB::table($questionTypesAnswers)
          ->where('question_type_id', $qTypeId)
          ->where('value', null)
          ->update([
              'value'   => 'Other',
              'options' => null,
          ]);
    }
}
