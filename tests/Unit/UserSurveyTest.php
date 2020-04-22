<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use App\Question;
use App\QuestionGroup;
use App\QuestionType;
use App\Survey;
use App\SurveyInstance;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

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
     * A survey can have multiple instances.
     */
    public function createAndAttachSurveyInstances()
    {
        foreach ($this->surveys as $survey) {
            $instances = $survey->instances()->createMany([
                [
                    'survey_id'  => $survey->id,
                    'year'       => $this->date->copy()->subYear(2)->year,
                ],
                [
                    'survey_id'  => $survey->id,
                    'year'       => $this->date->copy()->subYear(1)->year,
                ],
                [
                    'survey_id'  => $survey->id,
                    'year'       => $this->date->year,
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
     *Testing inverse of relationship on survey instance.
     */
    public function test_questions_can_be_created_for_each_survey()
    {
        $questionTypes = collect([
            QuestionType::CHECKBOX,
            QuestionType::TEXT,
        ]);

        foreach ($this->surveys as $survey) {
            $questions = [];

            for ($i = 0; $i < 10; $i++) {
                $group = QuestionGroup::create([
                    'body' => $this->faker->text,
                ]);
                $this->assertNotNull($group);

                //attach group for some questions
                if ($i >= 5 && $i <= 8) {
                    $belongsToGroup = true;
                } else {
                    $belongsToGroup = false;
                }

                $question = Question::create([
                    'survey_id'         => $survey->id,
                    'body'              => $this->faker->text,
                    'question_group_id' => $belongsToGroup
                        ? $group->id
                        : null,
                ]);

                $this->assertNotNull($question);
                if ($belongsToGroup) {
                    $this->assertNotNull($question->questionGroup);
                    $this->assertEquals($question->questionGroup->id, $group->id);
                } else {
                    $this->assertNull($question->questionGroup);
                }

                $type = $questionTypes->random();
                $questionType = $question->type()->create([
                    'answer_type' => $type,
                ]);

                if ($type = QuestionType::CHECKBOX) {
                    for ($a = 0; $a < 5; $a++) {
                        $questionType->questionTypeAnswers()->create([
                            'value'   => $this->faker->text,
                            'options' => [
                                'allow_custom_input' => true,
                            ],
                        ]);
                    }
                }
                $questions[] = $question;
            }
            $this->assertEquals(10, count($questions));
            foreach ($survey->instances as $instance) {
                $order = 1;
                $subOrder = 1;
                foreach ($questions as $question) {
                    if (! is_null($question->questionGroup)) {
                        $instance->questions()->attach(
                            $question->id,
                            [
                                'order'     => $order,
                                'sub_order' => $subOrder,
                            ]
                        );
                        $subOrder += 1;
                        if ($subOrder = 4) {
                            $order += 1;
                        }
                    } else {
                        $instance->questions()->attach(
                            $question->id,
                            [
                                'order' => $order,
                            ]
                        );
                        $order += 1;
                    }
                }
                $this->assertEquals($instance->questions()->whereNotNull('sub_order')->count(), 4);
            }
        }
    }

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->date = Carbon::now();

        $this->user = User::create([
            'first_name'        => $this->faker->name,
            'last_name'         => $this->faker->lastName,
            'display_name'      => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->date,
            'password'          => bcrypt('secret'),
            'remember_token'    => Str::random(10),
        ]);
        $this->assertNotNull($this->user);

        $this->createSurveys();
        $this->createAndAttachSurveyInstances();
    }
}
