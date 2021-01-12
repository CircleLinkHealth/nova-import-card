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

class HraQuestionsTest extends TestCase
{
    use DatabaseTransactions;
    use SetupTestSurveyData;

    /** @var SurveyInstance */
    private $hraSurvey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSurveyData();
        $this->hraSurvey = $this->user->getHRAInstances()->first();
    }

    public function test_hra_q_order_10()
    {
        $question = $this->getQuestion(10);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'No')->first();

        $this->postAnswer(['value' => 'No'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_11_a()
    {
        $this->test_hra_q_order_11_yes();
        $question = $this->getQuestion(11, 'a');

        $this->postAnswer(['value' => '10'], $question->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('11', $nextQuestion->pivot->order);
        $this->assertEquals('b', $nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_11_d()
    {
        $this->test_hra_q_order_11_yes();
        $question = $this->getQuestion(11, 'd');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('12', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_11_no()
    {
        $question = $this->getQuestion(11);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'No')->first();

        $this->postAnswer(['value' => 'No'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('12', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_11_yes()
    {
        $question = $this->getQuestion(11);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('11', $nextQuestion->pivot->order);
        $this->assertEquals('a', $nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_12_a()
    {
        $this->test_hra_q_order_12_yes();
        $question = $this->getQuestion(12, 'a');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', '<7 drinks per week')->first();

        $this->postAnswer(['value' => '<7 drinks per week'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('13', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_12_no()
    {
        $question = $this->getQuestion(12);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'No')->first();

        $this->postAnswer(['value' => 'No'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('13', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_12_yes()
    {
        $question = $this->getQuestion(12);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex, false);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('12', $nextQuestion->pivot->order);
        $this->assertEquals('a', $nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_18_any()
    {
        $question = $this->getQuestion(18);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Alcoholism or Drug Use')->first();

        $this->postAnswer([['name' => 'Alcoholism or Drug Use']], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('18', $nextQuestion->pivot->order);
        $this->assertEquals('a', $nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_18_none()
    {
        $question = $this->getQuestion(18);

        $this->postAnswer([], $question->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex, false);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('19', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_1_a()
    {
        $question = $this->getQuestion(1, 'a');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'White')->first();

        $this->postAnswer(['value' => 'White'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_1_b()
    {
        $question = $this->getQuestion(1, 'b');

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_2()
    {
        $question = $this->getQuestion(2);
        $this->postAnswer(['value' => '30'], $question->id);
    }

    public function test_hra_q_order_3()
    {
        $question = $this->getQuestion(3);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->first();

        $this->postAnswer(['feet' => 5, 'inches' => 6], $question->id, $answerType->id);
    }

    public function test_hra_q_order_32_disabled()
    {
        $question = $this->getQuestion(2);
        $this->postAnswer(['value' => '24'], $question->id);

        $question = $this->getQuestion(31);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('33', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_32_enabled()
    {
        $question = $this->getQuestion(2);
        $this->postAnswer(['value' => '30'], $question->id);

        $question = $this->getQuestion(31);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('32', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_35_disabled()
    {
        $question = $this->getQuestion(4);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Male')->first();

        $this->postAnswer(['value' => 'Male'], $question->id, $answerType->id);

        $question = $this->getQuestion(34);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('37', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_35_enabled()
    {
        $question = $this->getQuestion(4);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Female')->first();

        $this->postAnswer(['value' => 'Female'], $question->id, $answerType->id);

        $question = $this->getQuestion(34);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Yes')->first();

        $this->postAnswer(['value' => 'Yes'], $question->id, $answerType->id);

        $qIndex = $this->hraSurvey->questions->search(function (Question $q) use ($question) {
            return $q->id === $question->id;
        });

        $nextQuestion = $this->hraSurvey->getNextUnansweredQuestion($this->user, $qIndex);
        $this->assertNotNull($nextQuestion);
        $this->assertEquals('35', $nextQuestion->pivot->order);
        $this->assertNull($nextQuestion->pivot->sub_order);
    }

    public function test_hra_q_order_4()
    {
        $question = $this->getQuestion(4);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Male')->first();

        $this->postAnswer(['value' => 'Male'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_5()
    {
        $question = $this->getQuestion(5);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', 'Good')->first();

        $this->postAnswer(['value' => 'Good'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_6()
    {
        $question = $this->getQuestion(6);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', '0')->first();

        $this->postAnswer(['value' => '0'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_7()
    {
        $question = $this->getQuestion(7);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', '0')->first();

        $this->postAnswer(['value' => '0'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_8()
    {
        $question = $this->getQuestion(8);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', '0')->first();

        $this->postAnswer(['value' => '0'], $question->id, $answerType->id);
    }

    public function test_hra_q_order_9()
    {
        $question = $this->getQuestion(9);

        /** @var QuestionTypesAnswer $answerType */
        $answerType = $question->type->questionTypeAnswers()->where('value', '=', '0')->first();

        $this->postAnswer(['value' => '0'], $question->id, $answerType->id);
    }

    private function getQuestion($order, $subOrder = null): Question
    {
        //when and wherePivot do not work. ???
        if ($subOrder) {
            return $this->hraSurvey->questions()
                ->wherePivot('order', '=', $order)
                ->wherePivot('sub_order', '=', $subOrder)
                ->first();
        }

        return $this->hraSurvey->questions()
            ->wherePivot('order', '=', $order)
            ->first();
    }

    private function postAnswer($value, $questionId, $questionTypeAnswerId = null)
    {
        $patientId = $this->user->id;
        $req       = [
            'patient_id'         => $this->user->id,
            'survey_instance_id' => $this->hraSurvey->id,
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
            ->where('survey_instance_id', $this->hraSurvey->id)
            ->where('question_id', $questionId)
            ->first();

        $this->assertNotNull($answer);
        $this->assertEquals($value, $answer->value);
    }
}
