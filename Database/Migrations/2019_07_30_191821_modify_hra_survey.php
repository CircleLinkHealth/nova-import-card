<?php

use Illuminate\Database\Migrations\Migration;

class ModifyHraSurvey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var \App\SurveyQuestion $sQuestion */
        $sQuestion = \App\SurveyQuestion::where('order', '=', 46)
                                        ->first();

        if ( ! $sQuestion) {
            return;
        }

        $qType = \App\QuestionType::where('question_id', '=', $sQuestion->question_id)->first();

        if (!$qType) {
            return;
        }

        if (\App\QuestionTypesAnswer::where('question_type_id', '=', $qType->id)->exists()) {
            return;
        };

        \App\QuestionTypesAnswer::create([
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
