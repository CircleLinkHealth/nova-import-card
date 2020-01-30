<?php

namespace Tests\Feature;

use App\Answer;
use App\Question;
use App\QuestionTypesAnswer;
use App\SurveyInstance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\SetupTestSurveyData;
use Tests\TestCase;

class HraQuestionsTest extends TestCase
{
    use DatabaseTransactions,
        SetupTestSurveyData;

    /** @var SurveyInstance $hraSurvey */
    private $hraSurvey;

    public function setUp()
    {
        parent::setUp();
        $this->createTestSurveyData();
        $this->hraSurvey = $this->user->getHRAInstances()->first();
    }

    public function test_hra_q_order_1_a()
    {
        $question = $this->getQuestion(1, 'a');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'White')->first();

        $this->postAnswer('White', $question->id, $answerType->id);
    }

    public function test_hra_q_order_1_b()
    {
        $question = $this->getQuestion(1, 'b');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer('Yes', $question->id, $answerType->id);
    }

    private function getQuestion($order, $subOrder = null): Question
    {
        //when and wherePivot do not work. ???
        if ($subOrder) {
            return $this->hraSurvey->questions()
                                   ->wherePivot('order', '=', $order)
                                   ->wherePivot('sub_order', '=', $subOrder)
                                   ->first();
        } else {
            return $this->hraSurvey->questions()
                                   ->wherePivot('order', '=', $order)
                                   ->first();
        }
    }

    private function postAnswer($value, $questionId, $questionTypeAnswerId = null)
    {
        $patientId = $this->user->id;
        $response  = $this->actingAs($this->user)->json('POST', "/survey/hra/$patientId/save-answer", [
            'patient_id'              => $this->user->id,
            'survey_instance_id'      => $this->hraSurvey->id,
            'question_id'             => $questionId,
            'question_type_answer_id' => $questionTypeAnswerId,
            'value'                   => ['value' => $value],
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'created' => true,
            ]);

        /** @var Answer $answer */
        $answer = $this->user->answers()
                             ->where('survey_instance_id', $this->hraSurvey->id)
                             ->where('question_id', $questionId)
                             ->first();

        $this->assertNotNull($answer);
        $this->assertEquals($value, $answer->value['value']);
    }
}
