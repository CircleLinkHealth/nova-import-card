<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class HraQ1Q12Q5CopyChanges extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (isUnitTestingEnv()) {
            return;
        }
        $questionTypes        = 'question_types';
        $questionTypesAnswers = 'question_types_answers';
        $questionsTable       = 'questions';
        $questionGroups       = 'question_groups';
        $surveysTable         = 'surveys';
        $surveyQuestionsTable = 'survey_questions';

        $hraSurvey = DB::table($surveysTable)
            ->where('name', 'HRA')
            ->first();

        if ( ! $hraSurvey) {
            return;
        }

        $hraSurveyId = $hraSurvey->id;

        $now = Carbon\Carbon::now();

        // Q1
        DB::table($questionTypesAnswers)
            ->where('value', 'Black/African-Ameri.')
            ->update([
                'value'      => 'African American/Black',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'White')
            ->update([
                'value'      => 'Caucasian/White',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'Native Ameri./Alaskan')
            ->update([
                'value'      => 'Native American or Alaskan Native',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'Native Hawaiian')
            ->update([
                'value'      => 'Native Hawaiian or other Pacific Islander',
                'updated_at' => $now,
            ]);

        // Q1 split to a and b
        $toDelete = DB::table($questionTypesAnswers)
            ->where('value', 'Hispanic or Latino Origin or Descent')
            ->first()->id;

        if ($toDelete) {
            DB::table($questionTypesAnswers)
                ->delete($toDelete->id);
        }

        $toDeleteId = DB::table($questionGroups)
            ->where('body', 'What is your race and ethnicity')
            ->first()->id;

        DB::table($questionGroups)
            ->delete($toDeleteId);

        $oldQuestionId = DB::table($questionsTable)
            ->where('body', 'What is your race?')
            ->first()->id;

        DB::table($questionsTable)
            ->where('id', $oldQuestionId)
            ->update([
                'question_group_id' => null,
                'updated_at'        => $now,
            ]);

        DB::table($surveyQuestionsTable)
            ->where('question_id', $oldQuestionId)
            ->update([
                'sub_order'  => null,
                'updated_at' => $now,
            ]);

        $newQuestionId = DB::table($questionsTable)
            ->where('body', 'Are you Hispanic or Latino?')
            ->first()->id;

        DB::table($questionsTable)
            ->delete($newQuestionId);

        $surveyInstanceId = DB::table($surveyQuestionsTable)
            ->where('question_id', $oldQuestionId)
            ->first()->survey_instance_id;

        $toDeleteId3 = DB::table($surveyQuestionsTable)
            ->where('question_id', $newQuestionId)
            ->first()->id;

        DB::table($surveyQuestionsTable)
            ->delete($toDeleteId3);

        $newQuestionTypeId = DB::table($questionTypes)
            ->where('question_id', $newQuestionId)
            ->first()->id;

        $typeAnswer1 = DB::table($questionTypesAnswers)
            ->where('question_type_id', $newQuestionTypeId)
            ->where('value', 'Yes')
            ->first()->id;

        $typeAnswer2 = DB::table($questionTypesAnswers)
            ->where('question_type_id', $newQuestionTypeId)
            ->where('value', 'No')
            ->first()->id;

        DB::table($questionTypesAnswers)
            ->delete($typeAnswer1);

        DB::table($questionTypesAnswers)
            ->delete($typeAnswer2);

        DB::table($questionTypes)
            ->delete($newQuestionTypeId);

        // Q12
        DB::table($questionTypesAnswers)
            ->where('value', 'I used to, but now I am sober')
            ->update([
                'value'      => 'Yes, but i am now sober',
                'updated_at' => $now,
            ]);

        // Q15
        DB::table($questionsTable)
            ->where('body', 'Do you practice safe sex by using condoms or dental dams?')
            ->update([
                'body'       => 'Are you practicing safe sex?',
                'updated_at' => $now,
            ]);
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (isUnitTestingEnv()) {
            return;
        }
        $questionTypes        = 'question_types';
        $questionTypesAnswers = 'question_types_answers';
        $questionsTable       = 'questions';
        $questionGroups       = 'question_groups';
        $surveysTable         = 'surveys';
        $surveyQuestionsTable = 'survey_questions';

        $hraSurvey = DB::table($surveysTable)
            ->where('name', 'HRA')
            ->first();

        if ( ! $hraSurvey) {
            return;
        }

        $hraSurveyId = $hraSurvey->id;

        $now = Carbon\Carbon::now();

        // Q1
        DB::table($questionTypesAnswers)
            ->where('value', 'African American/Black')
            ->update([
                'value'      => 'Black/African-Ameri.',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'Caucasian/White')
            ->update([
                'value'      => 'White',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'Native American or Alaskan Native')
            ->update([
                'value'      => 'Native Ameri./Alaskan',
                'updated_at' => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->where('value', 'Native Hawaiian or other Pacific Islander')
            ->update([
                'value'      => 'Native Hawaiian',
                'updated_at' => $now,
            ]);

        // Q1 split to a and b
        $toDeleteId = DB::table($questionTypesAnswers)
            ->where('value', 'Hispanic or Latino Origin or Descent')
            ->first()->id;

        DB::table($questionTypesAnswers)
            ->delete($toDeleteId);

        DB::table($questionGroups)
            ->insert([
                'body'       => 'What is your race and ethnicity?',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        $questionGroupId = DB::table($questionGroups)
            ->where('body', 'What is your race and ethnicity?')
            ->first()->id;

        $oldQuestionId = DB::table($questionsTable)
            ->where('body', 'What is your race?')
            ->first()->id;

        DB::table($questionsTable)
            ->where('id', $oldQuestionId)
            ->update([
                'question_group_id' => $questionGroupId,
                'updated_at'        => $now,
            ]);

        DB::table($surveyQuestionsTable)
            ->where('question_id', $oldQuestionId)
            ->update([
                'sub_order'  => 'a',
                'updated_at' => $now,
            ]);

        DB::table($questionsTable)
            ->insert([
                'survey_id'         => $hraSurveyId,
                'body'              => 'Are you Hispanic or Latino?',
                'optional'          => 0,
                'conditions'        => null,
                'question_group_id' => $questionGroupId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

        $surveyInstanceId = DB::table($surveyQuestionsTable)
            ->where('question_id', $oldQuestionId)
            ->first()->survey_instance_id;

        DB::table($surveyQuestionsTable)
            ->insert([
                'survey_instance_id' => $surveyInstanceId,
                'question_id'        => $newQuestionId,
                'order'              => 1,
                'sub_order'          => 'b',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

        DB::table($questionTypes)
            ->insert([
                'question_id' => $newQuestionId,
                'type'        => 'radio',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

        $newQuestionTypeId = DB::table($questionTypes)
            ->where('question_id', $newQuestionId)
            ->first()->id;

        $yesNoQuestion = ['yes_or_no_question' => true];

        DB::table($questionTypesAnswers)
            ->insert([
                'question_type_id' => $newQuestionTypeId,
                'value'            => 'Yes',
                'options'          => json_encode($yesNoQuestion),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

        DB::table($questionTypesAnswers)
            ->insert([
                'question_type_id' => $newQuestionTypeId,
                'value'            => 'No',
                'options'          => json_encode($yesNoQuestion),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

        // Q12
        DB::table($questionTypesAnswers)
            ->where('value', 'Yes, but i am now sober')
            ->update([
                'value'      => 'I used to, but now I am sober',
                'updated_at' => $now,
            ]);

        // Q15
        DB::table($questionsTable)
            ->where('body', 'Are you practicing safe sex?')
            ->update([
                'body'       => 'Do you practice safe sex by using condoms or dental dams?',
                'updated_at' => $now,
            ]);
    }
}
