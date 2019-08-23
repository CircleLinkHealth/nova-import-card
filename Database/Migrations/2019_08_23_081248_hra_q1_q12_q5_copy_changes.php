<?php

use Illuminate\Database\Migrations\Migration;

class HraQ1Q12Q5CopyChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //TODO:

        // search and replace for all question type answers that were changed (Q1, Q12, Q15)
        // Q1:
        // add new question sub_order b to q1
        // find all places where q1 is read

        $questionTypes        = "question_types";
        $questionTypesAnswers = "question_types_answers";
        $questionsTable       = "questions";
        $questionGroups       = "question_groups";
        $surveysTable         = "surveys";
        $surveyQuestionsTable = "survey_questions";

        $hraSurveyId = DB::table($surveysTable)
                         ->where('name', 'HRA')
                         ->first()->id;

        // Q1
        DB::table($questionTypesAnswers)
          ->where('value', 'African American/Black')
          ->update(['value' => 'Black/African-Ameri.']);

        DB::table($questionTypesAnswers)
          ->where('value', 'Caucasian/White')
          ->update(['value' => 'White']);

        DB::table($questionTypesAnswers)
          ->where('value', 'Native American or Alaskan Native')
          ->update(['value' => 'Native Ameri./Alaskan']);

        DB::table($questionTypesAnswers)
          ->where('value', 'Native Hawaiian or other Pacific Islander')
          ->update(['value' => 'Native Hawaiian']);

        // Q1 split to a and b
        DB::table($questionTypesAnswers)
          ->delete()
          ->where('value', 'Hispanic or Latino Origin or Descent');

        DB::table($questionGroups)
          ->insert(['body' => 'What is your race and ethnicity']);

        $questionGroupId = DB::table($questionGroups)
                             ->where('body', 'What is your race and ethnicity')
                             ->first()->id;

        $oldQuestionId = DB::table($questionsTable)
                           ->where('body', 'What is your race?')
                           ->first()->id;

        DB::table($questionsTable)
          ->where('id', $oldQuestionId)
          ->update(['question_group_id' => $questionGroupId]);

        DB::table($surveyQuestionsTable)
          ->where('question_id', $oldQuestionId)
          ->update(['sub_order' => 'a']);

        DB::table($questionsTable)
          ->insert([
              'survey_id'         => $hraSurveyId,
              'body'              => 'Are you Hispanic or Latino?',
              'optional'          => 0,
              'conditions'        => '',
              'question_group_id' => $questionGroupId,
          ]);

        $newQuestionId = DB::table($questionsTable)
                           ->where('body', 'Are you Hispanic or Latino?')
                           ->first()->id;

        $surveyInstanceId = DB::table($surveyQuestionsTable)
                              ->where('question_id', $oldQuestionId)
                              ->first()->survey_instance_id;

        DB::table($surveyQuestionsTable)
          ->insert([
              'survey_instance_id' => $surveyInstanceId,
              'question_id'        => $newQuestionId,
              'order'              => 1,
              'sub_order'          => 'b',
          ]);

        DB::table($questionTypes)
          ->insert(['question_id' => $newQuestionId, 'type' => 'radio']);

        $newQuestionTypeId = DB::table($questionTypes)
                               ->where('question_id', $newQuestionId)
                               ->first()->id;

        $yesNoQuestion = ['yes_or_no_question' => true];

        DB::table($questionTypesAnswers)
          ->insert(['question_type_id' => $newQuestionTypeId, 'value' => 'Yes', 'options' => json_encode($yesNoQuestion)]);

        DB::table($questionTypesAnswers)
          ->insert(['question_type_id' => $newQuestionTypeId, 'value' => 'No', 'options' => json_encode($yesNoQuestion)]);

        // Q12
        DB::table($questionTypesAnswers)
          ->where('value', 'Yes, but i am now sober')
          ->update(['value' => 'I used to, but now I am sober']);

        // Q15
        DB::table($questionsTable)
          ->where('body', 'Are you practicing safe sex?')
          ->update(['body' => 'Do you practice safe sex by using condoms or dental dams?']);

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
