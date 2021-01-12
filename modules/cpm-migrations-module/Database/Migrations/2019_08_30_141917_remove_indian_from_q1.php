<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class RemoveIndianFromQ1 extends Migration
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
            ->where('body', 'What is your race?')
            ->first()->id;

        if ($q) {
            $qType = DB::table($questionTypes)
                ->where('question_id', $q->id)
                ->first();

            if ($qType) {
                DB::table($questionTypesAnswers)
                    ->insert([
                        'question_type_id' => $qType->id,
                        'value'            => 'Indian',
                    ]);
            }
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionTypesAnswers = 'question_types_answers';
        $qTypeAnswer          = DB::table($questionTypesAnswers)
            ->where('value', 'Indian')
            ->first();

        if ($qTypeAnswer) {
            DB::table($questionTypesAnswers)
                ->delete($qTypeAnswer->id);
        }
    }
}
