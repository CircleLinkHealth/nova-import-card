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


    /**
     * Questions belong to a survey, but will be included in the survey depending on the survey instance,
     * in survey_questions table
     */
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
     * Each survey instance will hold all the questions in the intermediary table survey_questions,
     * along with the order of the question in the survey
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

    /**
     * Question has one type, checkbox, text, radio, range etc
     */
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

    /**
     * In the case a Question is checkbox, radio buttons etc, we store possible answers in question_types_answers
     * table. (optional) Any specific characteristics of that possible answer are stored in 'options'. In this case
     * 'allow_custom_input' could mean that in case that specific option checkbox is selected, the user will be able to
     * also input extra text.
     *
     */
    public function test_question_has_possible_answers()
    {

        $question = $this->createAndAttachQuestionToSurvey();

        $type = $question->type()->create([
            'answer_type' => QuestionType::CHECKBOX,
        ]);

        $possibleAnswer = $type->questionTypeAnswers()->create([
            'value'   => 'Test answer',
            'options' => [
                'allow_custom_input' => true,
            ],
        ]);

        $this->assertNotNull($possibleAnswer);
        $this->assertTrue(array_key_exists('allow_custom_input', $possibleAnswer->options));

    }

    /**
     * Users can have many answers.
     * Answers are related to: Users, Survey Instances, Questions, Question Type Answers (optional/nullable)
     * Value_1 is required.
     * Value_2 exists in the cases of e.g. a selected possible answer + extra custom input (nullable)
     */
    public function test_user_can_have_answers()
    {

        $question = $this->createAndAttachQuestionToSurvey();

        $type = $question->type()->create([
            'answer_type' => QuestionType::CHECKBOX,
        ]);

        $possibleAnswer = $type->questionTypeAnswers()->create([
            'value'   => 'Test answer',
            'options' => [
                'allow_custom_input' => true,
            ],
        ]);

        $answer = $this->user->answers()->create([
            'survey_instance_id'      => $this->surveyInstance->id,
            'question_id'             => $question->id,
            'question_type_answer_id' => $possibleAnswer->id,
            'value_1'                 => 'Test answer',
            'value_2'                 => 'Test answer from the custom input',
        ]);

        $this->assertNotNull($answer);
        $this->assertNotNull($answer->user);
        $this->assertNotNull($answer->surveyInstance);
        $this->assertNotNull($answer->question);
        $this->assertNotNull($answer->questionTypesAnswer);

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

        $this->assertNotNull($question);

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
