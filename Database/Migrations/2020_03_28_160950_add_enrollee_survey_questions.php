<?php

use App\Survey;
use App\SurveyInstance;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class AddEnrolleeSurveyQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $time = Carbon::now();
        $enrolleesSurvey = Survey::firstOrCreate([
            'name' => Survey::ENROLLEES,
            'description' => 'Enrollees Survey',
        ]);
        $currentInstance = SurveyInstance::firstOrCreate([
            'survey_id' => $enrolleesSurvey->id,
            'year' => $time->year,
        ]);

        $seed = app(SurveySeeder::class);
        $questionsData = $seed->enrolleesQuestionData();
        $seed->createQuestions($currentInstance,$questionsData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
