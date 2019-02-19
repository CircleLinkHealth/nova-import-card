<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\SetupTestSurveyData;
use Tests\TestCase;

class SurveyQuestionsAnswersTest extends TestCase
{
    use DatabaseTransactions,
        SetupTestSurveyData;


    public function test_user_can_answer_and_update_question()
    {
        $this->withoutExceptionHandling();
        $surveyInstance = $this->user->getHRAInstances()->first();
        $question       = $surveyInstance->questions()->with('type')->first();

        $response = $this->actingAs($this->user)->json('POST', '/survey/answer', [
            'user_id'            => $this->user->id,
            'survey_instance_id' => $surveyInstance->id,
            'question_id'        => $question->id,
            'value_1'            => $this->faker->text,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'created' => true,
            ]);

        $answer = $this->user->answers()->where('survey_instance_id', $surveyInstance->id)->where('question_id', $question->id)->first();

        $this->assertNotNull($answer);

    }

    public function test_user_gets_survey_data()
    {
        $this->withoutExceptionHandling();
        $survey = $this->user->getSurveys()->first();

        $response = $this->actingAs($this->user)->json('GET', '/survey', [
            'survey_id' => $survey->id
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue(true);
        //todo:test data structure
        //todo:test redirect if user is not logged in
    }

    public function setUp()
    {
        parent::setUp();
        $this->createTestSurveyData();
    }
}
