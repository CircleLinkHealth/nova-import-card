<?php

namespace Tests\Unit;

use App\Question;
use App\QuestionType;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\SetupTestSurveyData;
use Tests\TestCase;
use Faker\Factory;

class UserSurveyTest extends TestCase
{
    private $faker;
    private $date;
    private $user;
    private $surveys;

    use DatabaseTransactions;

    /**
     * A user can have multiple surveys, and each survey can have multiple instances, with variable statuses of
     * completion for that user. These relationships exist in the intermediary table 'users_survey', with pivot value
     * 'status'.
     *
     *
     * @return void
     */
    private function createSurveys()
    {
        Survey::create(
            [
                'name'        => Survey::HRA,
                'description' => 'Health Risk Assessment',
            ]);
        Survey::create(
            [
                'name'        => Survey::VITALS,
                'description' => 'Vitals Report',
            ]);

        $this->surveys = Survey::get();
        $this->assertEquals(2, $this->surveys->count());

    }

    /**
     * A survey can have multiple instances
     *
     */
    public function createAndAttachSurveyInstances()
    {

        foreach ($this->surveys as $survey) {
            $instances = $survey->instances()->createMany([
                [
                    'survey_id'  => $survey->id,
                    'name'       => $survey->name . ' ' . $this->date->copy()->subYear(2)->year,
                    'start_date' => $this->date->copy()->subYear(2)->startOfYear()->toDateString(),
                    'end_date'   => $this->date->copy()->subYear(2)->endOfYear()->toDateString(),
                ],
                [
                    'survey_id'  => $survey->id,
                    'name'       => $survey->name . ' ' . $this->date->copy()->subYear(1)->year,
                    'start_date' => $this->date->copy()->subYear(1)->startOfYear()->toDateString(),
                    'end_date'   => $this->date->copy()->subYear(1)->endOfYear()->toDateString(),
                ],
                [
                    'survey_id'  => $survey->id,
                    'name'       => $survey->name . ' ' . $this->date->year,
                    'start_date' => $this->date->copy()->startOfYear()->toDateString(),
                    'end_date'   => $this->date->copy()->endOfYear()->toDateString(),
                ],
            ]);
            $this->assertEquals(3, $instances->count());
            foreach ($instances as $instance) {
                //return true? test
                $this->user->surveys()->attach(
                    $survey->id,
                    [
                        'survey_instance_id' => $instance->id,
                        'status'             => SurveyInstance::PENDING,
                    ]
                );
            }
        }
        $this->assertEquals(2, $this->user->getSurveys()->count());
        $this->assertEquals(3, $this->user->getSurveys()->first()->instances()->count());
        $this->assertEquals(3, $this->user->getSurveys()->last()->instances()->count());

    }

    /**
     *Testing inverse of relationship on survey instance
     */
    public function test_questions_can_be_created_for_each_survey()
    {

        $questionTypes = collect([
            QuestionType::CHECKBOX,
            QuestionType::TEXT
        ]);

        foreach ($this->surveys as $survey) {
            $questions = [];
            $order     = [];
            for ($i = 1; $i < 11; $i++) {
                $question = Question::create([
                    'survey_id' => $survey->id,
                    'body'      => $this->faker->text,
                ]);
                $type = $questionTypes->random();
                $questionType = $question->type()->create([
                    'answer_type' => $type,
                ]);
                if($type = QuestionType::CHECKBOX){
                    for ($a = 0; $a < 5; $a++){
                        $questionType->questionTypeAnswers()->create([
                            'value'   => $this->faker->text,
                            'options' => [
                                'allow_custom_input' => true,
                            ],
                        ]);
                    }
                }
                $questions[] = $question;
                $order[] = $i;
            }
            $this->assertEquals(10, count($questions));
            foreach ($survey->instances as $instance) {
                $instanceOrder = collect($order);
                foreach ($questions as $question) {
                    if ($this->faker->boolean(80)) {
                        $instance->questions()->attach(
                            $question->id,
                            [
                                'order'   => $instanceOrder->shift(),
                            ]
                        );
                    }
                }
            }
            //todo: test question order is unique
            //test each instance has a good number of questions
            //copy to trait
        }

    }

    public function setUp()
    {
        parent::setUp();

        $this->faker = $faker = Factory::create();

        $this->date  = Carbon::now();

        $this->user = User::create([
            'name'              => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->date,
            'password'          => bcrypt('secret'),
            'remember_token'    => str_random(10),
        ]);
        $this->assertNotNull($this->user);

        $this->createSurveys();
        $this->createAndAttachSurveyInstances();


    }
}
