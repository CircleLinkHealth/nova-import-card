<?php

namespace Tests\Unit;

use App\Question;
use App\QuestionType;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SurveyQuestionsAnswersTest extends TestCase
{
    private $user;
    private $survey;
    private $surveyInstance;

    use DatabaseTransactions;


    public function test_questions_belongs_to_a_survey()
    {

        $question = Question::create([
            'survey_id' => $this->survey->id,
            'body'      => 'Test Question?',
        ]);

        $survey = $question->survey;

        $this->assertNotNull($survey);
        $this->assertNotNull($survey->name);
    }


    /**
     * Each survey instance will hold all the questions in the intermediary table survey_questions
     *
     * @return void
     */
    public function test_survey_instance_has_questions()
    {
        $this->createAndAttachQuestionToSurvey();

        $questions = $this->surveyInstance->questions()->get();

        $this->assertNotNull($questions);
        $this->assertNotNull($questions->first()->body);
        $this->assertEquals($questions->first()->pivot->order, 1);
    }

    public function test_question_has_type()
    {
        $question = $this->createAndAttachQuestionToSurvey();

        $type = $question->type()->create([
            'answer_type' => QuestionType::TEXT,
        ]);

        $this->assertNotNull($type);
        $this->assertEquals($question->type->answer_type, 'text');

        $questionFromType = $type->question;
        $this->assertNotNull($questionFromType);
        $this->assertEquals($questionFromType->body, 'Test Question?');

    }

    public function test_question_has_possible_answers()
    {

        $question = $this->createAndAttachQuestionToSurvey();

        $type = $question->type()->create([
            'answer_type' => QuestionType::CHECKBOX,
        ]);

        $possibleAnswer = $type->possibleAnswers()->create([
            'value'   => 'Test answer',
            'options' => [
                'allow_custom_input' => true
            ]
        ]);

        $this->assertNotNull($possibleAnswer);
        $this->assertTrue(array_key_exists('allow_custom_input',$possibleAnswer->options));

    }

    public function test_user_can_have_answers(){

    }

    public function createAndAttachQuestionToSurvey()
    {

        $question = Question::create([
            'survey_id' => $this->survey->id,
            'body'      => 'Test Question?',
        ]);
        $this->surveyInstance->questions()->attach($question->id, [
            'order' => 1,
        ]);

        return $question;
    }

    public function setUp()
    {
        parent::setUp();

        $this->user = User::create([
            'email'    => 'test@test.com',
            'name'     => 'Test',
            'password' => bcrypt('test'),
        ]);

        $this->survey = Survey::create([
            'name'        => 'Test Survey',
            'description' => 'This is a test description',
        ]);

        $this->surveyInstance = SurveyInstance::create([
            'survey_id'  => $this->survey->id,
            'name'       => 'TEST 2019',
            'start_date' => '2019-01-01',
            'end_date'   => '2019-01-01',
        ]);

    }
}
