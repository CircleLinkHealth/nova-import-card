<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ModifyHraSurvey extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $sQuestion = DB::table('survey_questions')->where('order', '=', 46)->first();

        if ( ! $sQuestion) {
            return;
        }

        $qType = DB::table('question_types')->where('question_id', '=', $sQuestion->question_id)->first();

        if ( ! $qType) {
            return;
        }

        $existsInQuestionTypesAnswer = DB::table('question_types_answers')->where('question_type_id', '=', $qType->id)->exists();

        if (1 === $existsInQuestionTypesAnswer) {
            return;
        }

        DB::table('question_types_answers')->insert([
            'question_type_id' => $qType->id,
            'value'            => null,
            'options'          => [
                'title'          => '',
                'placeholder'    => 'Type response here...',
                'allow_multiple' => false,
                'key'            => 'value',
            ],
        ]);
    }
}
