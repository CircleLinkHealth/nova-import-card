<?php

namespace Tests\Helpers;

use App\Question;
use App\QuestionGroup;
use App\QuestionType;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Support\Collection;

trait SetupTestSurveyData
{
    protected $faker;

    /** @var Carbon */
    protected $date;

    /** @var \App\User */
    protected $user;

    /** @var Collection */
    protected $surveys;

    /**
     * Creates User.
     */
    public function createUser()
    {
        $this->user = User::create([
            'first_name'        => $this->faker->name,
            'last_name'         => $this->faker->lastname,
            'display_name'      => $this->faker->name,
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->date,
            'password'          => bcrypt('secret'),
            'remember_token'    => str_random(10),
        ]);

        $this->assertNotNull($this->user);
    }

    /**
     *  Creates Surveys.
     */
    public function createSurveys()
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

    public function createAndAttachSurveyInstances()
    {
        foreach ($this->surveys as $survey) {
            $instances = $survey->instances()->createMany([
                [
                    'survey_id' => $survey->id,
                    'year'      => $this->date->copy()->subYear(2)->year,
                ],
                [
                    'survey_id' => $survey->id,
                    'year'      => $this->date->copy()->subYear(1)->year,
                ],
                [
                    'survey_id' => $survey->id,
                    'year'      => $this->date->year,
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
    }

    public function createQuestionsForEachSurveyInstance()
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
                    'optional'          => rand(0, 1),
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
                    'type' => $type,
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

    public function createTestSurveyData()
    {
        $this->faker = $faker = Factory::create();
        $this->date = Carbon::now();

        $this->createUser();

        $this->createAndAttachSurveysNew();

        /*
        $this->createSurveys();

        $this->createAndAttachSurveyInstances();

        $this->createQuestionsForEachSurveyInstance();
        */
    }

    public function createAndAttachSurveysNew()
    {
        (new \SurveySeeder())->run();

        $this->surveys = Survey::whereIn('name', [Survey::HRA, Survey::VITALS])->get();
        $this->assertEquals(2, $this->surveys->count());

        foreach ($this->surveys as $survey) {
            $instances = $survey->instances;
            $this->assertEquals(1, $instances->count());
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
    }
}
