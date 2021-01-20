<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ModifyQ11cToAllowCustomInput extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questionTypes        = 'question_types';
        $questionTypesAnswers = 'question_types_answers';
        $questionsTable       = 'questions';

        $q = DB::table($questionsTable)
            ->where('body', 'On average, how many packs/day do or did you smoke?')
            ->first();

        if ( ! $q) {
            return;
        }

        $qType = DB::table($questionTypes)
            ->where('question_id', $q->id)
            ->first();

        if ( ! $qType) {
            return;
        }

        DB::table($questionTypesAnswers)
            ->where('question_type_id', $qType->id)
            ->where('value', null)
            ->update([
                'value'   => 'Other',
                'options' => null,
            ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionTypes        = 'question_types';
        $questionTypesAnswers = 'question_types_answers';
        $questionsTable       = 'questions';

        $q = DB::table($questionsTable)
            ->where('body', 'On average, how many packs/day do or did you smoke?')
            ->first();

        if ( ! $q) {
            return;
        }

        $qType = DB::table($questionTypes)
            ->where('question_id', $q->id)
            ->first();

        if ( ! $qType) {
            return;
        }

        DB::table($questionTypesAnswers)
            ->where('question_type_id', $qType->id)
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
}
