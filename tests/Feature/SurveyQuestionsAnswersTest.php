<?php

namespace Tests\Unit;

use App\Survey;
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
        $question = $surveyInstance->questions()->with('type')->first();

        $patientId = $this->user->id;
        $response = $this->actingAs($this->user)->json('POST', "/survey/hra/$patientId/save-answer", [
            'patient_id'         => $this->user->id,
            'survey_instance_id' => $surveyInstance->id,
            'question_id'        => $question->id,
            'value'              => $this->faker->text,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'created' => true,
            ]);

        $answer = $this->user->answers()->where('survey_instance_id', $surveyInstance->id)->where('question_id',
            $question->id)->first();

        $this->assertNotNull($answer);
    }

    public function test_user_gets_survey_data()
    {
        $this->withoutExceptionHandling();

        /** @var Survey $survey */
        $survey = $this->user->getSurveys()->first();

        $patientId = $this->user->id;
        $name = strtolower($survey->name);

        $response = $this->actingAs($this->user)->get("/survey/$name/$patientId/$survey->id");
        $response->assertStatus(200);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSurveyData();
    }
}
