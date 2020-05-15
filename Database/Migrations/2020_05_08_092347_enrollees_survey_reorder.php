<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class EnrolleesSurveyReorder extends Migration
{
    const CONFIRM_ADDRESS  = 'Q_CONFIRM_ADDRESS';
    const CONFIRM_LETTER   = 'Q_CONFIRM_LETTER';
    const ENROLLEES        = 'Enrollees';
    const PREFERRED_DAYS   = 'Q_PREFERRED_DAYS';
    const PREFERRED_NUMBER = 'Q_PREFERRED_NUMBER';
    const PREFERRED_TIME   = 'Q_PREFERRED_TIME';
    const REQUESTS_INFO    = 'Q_REQUESTS_INFO';

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $survey = DB::table('surveys')
            ->where('name', '=', self::ENROLLEES)
            ->first();

        if ( ! $survey) {
            return;
        }

        $surveyId = $survey->id;

        DB::table('questions')
            ->where('survey_id', '=', $surveyId)
            ->where('identifier', '=', self::PREFERRED_NUMBER)
            ->update([
                'body' => 'Please confirm or input the best number for the nurse to call',
            ]);

        DB::table('questions')
            ->where('survey_id', '=', $surveyId)
            ->where('identifier', '=', self::PREFERRED_DAYS)
            ->update([
                'question_group_id' => null,
                'body'              => "Choose the days you're free for a nurse call:",
            ]);

        DB::table('questions')
            ->where('survey_id', '=', $surveyId)
            ->where('identifier', '=', self::PREFERRED_TIME)
            ->update([
                'question_group_id' => null,
                'body'              => "Choose the times you're free for a nurse call:",
            ]);

        $this->reOrderQuestion($surveyId, self::PREFERRED_DAYS, 3);
        $this->reOrderQuestion($surveyId, self::PREFERRED_TIME, 4);
        $this->reOrderQuestion($surveyId, self::REQUESTS_INFO, 5);
        $this->reOrderQuestion($surveyId, self::CONFIRM_ADDRESS, 6);
        $this->reOrderQuestion($surveyId, self::CONFIRM_LETTER, 7);

        DB::table('question_groups')
            ->where('body', '=', 'Please choose preferred days and time to contact:')
            ->delete();
    }

    /**
     * NOTE: this function reorders questions for all survey instances.
     *       not taking this into account now, because at this point, there is only
     *       one survey instance.
     *
     * @param $surveyId
     * @param $questionIdentifier
     * @param $order
     * @param null $subOrder
     */
    private function reOrderQuestion($surveyId, $questionIdentifier, $order, $subOrder = null)
    {
        $q = DB::table('questions')
            ->where('survey_id', '=', $surveyId)
            ->where('identifier', '=', $questionIdentifier)
            ->first();

        if ( ! $q) {
            return;
        }

        DB::table('survey_questions')
            ->where('question_id', '=', $q->id)
            ->update([
                'order'     => $order,
                'sub_order' => $subOrder,
            ]);
    }
}
