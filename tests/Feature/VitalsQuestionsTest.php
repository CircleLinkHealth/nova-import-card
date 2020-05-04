<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Answer;
use App\Question;
use App\QuestionTypesAnswer;
use App\SurveyInstance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\SetupTestSurveyData;
use Tests\TestCase;

class VitalsQuestionsTest extends TestCase
{
    use DatabaseTransactions;
    use SetupTestSurveyData;

    /** @var SurveyInstance */
    private $vitalsSurvey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSurveyData();
        $this->vitalsSurvey = $this->user->getVitalsInstances()->first();
    }

    public function test_vitals_q_order_1()
    {
        $question = $this->getQuestion(1);

        $this->postAnswer(['first_metric' => 18, 'second_metric' => 4], $question->id);
    }

    public function test_vitals_q_order_2()
    {
        $question = $this->getQuestion(2);

        $this->postAnswer(['value' => 54], $question->id);
    }

    public function test_vitals_q_order_3()
    {
        $question = $this->getQuestion(3);

        $this->postAnswer(['feet' => 5, 'inches' => 10], $question->id);
    }

    public function test_vitals_q_order_4()
    {
        $question = $this->getQuestion(4);

        $this->postAnswer(['value' => 2.13], $question->id);
    }

    public function test_vitals_q_order_5_a()
    {
        $question = $this->getQuestion(5, 'a');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 2)->first();

        $this->postAnswer(['value' => 2], $question->id, $answerType->id);
    }

    public function test_vitals_q_order_5_b()
    {
        $question = $this->getQuestion(5, 'b');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 2)->first();

        $this->postAnswer(['value' => 2], $question->id, $answerType->id);

        $qIndex = $this->vitalsSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->vitalsSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals(5, $nextQuestion->pivot->order);
        $this->assertEquals('c', $nextQuestion->pivot->sub_order);
    }

    public function test_vitals_q_order_5_c()
    {
        $question = $this->getQuestion(5, 'c');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 4)->first();

        $this->postAnswer(['value' => 4], $question->id, $answerType->id);

        $qIndex = $this->vitalsSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->vitalsSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNull($nextQuestion);
    }

    public function test_vitals_survey_complete()
    {
        $this->test_vitals_q_order_1();
        $this->test_vitals_q_order_2();
        $this->test_vitals_q_order_3();
        $this->test_vitals_q_order_4();
        $this->test_vitals_q_order_5_a();
        $this->test_vitals_q_order_5_b();
        $this->test_vitals_q_order_5_c();

        $nextQuestion = $this->vitalsSurvey->getNextUnansweredQuestion($this->user);
        $this->assertNull($nextQuestion);
    }

    private function getQuestion($order, $subOrder = null): Question
    {
        //when and wherePivot do not work. ???
        if ($subOrder) {
            return $this->vitalsSurvey->questions()
                ->wherePivot('order', '=', $order)
                ->wherePivot('sub_order', '=', $subOrder)
                ->first();
        }

        return $this->vitalsSurvey->questions()
            ->wherePivot('order', '=', $order)
            ->first();
    }

    private function postAnswer($value, $questionId, $questionTypeAnswerId = null)
    {
        $patientId = $this->user->id;
        $req       = [
            'patient_id'         => $this->user->id,
            'survey_instance_id' => $this->vitalsSurvey->id,
            'question_id'        => $questionId,
            'value'              => $value,
        ];

        if ($questionTypeAnswerId) {
            $req['question_type_answer_id'] = $questionTypeAnswerId;
        }

        $response = $this->actingAs($this->user)->json('POST', "/survey/hra/$patientId/save-answer", $req);
        $response
            ->assertStatus(200)
            ->assertJson([
                'created' => true,
            ]);

        /** @var Answer $answer */
        $answer = $this->user->answers()
            ->where('survey_instance_id', $this->vitalsSurvey->id)
            ->where('question_id', $questionId)
            ->first();

        $this->assertNotNull($answer);
        $this->assertEquals($value, $answer->value);
    }
}
