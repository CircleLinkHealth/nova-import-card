<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Services\SurveyService;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PatientSurveyAnswersSeeder extends Seeder
{
    /**
     * use this to create test patient, currently not being used.
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function createTestUser()
    {
        $faker = Factory::create();

        $user = User::create([
            'first_name'   => $faker->name,
            'last_name'    => $faker->name,
            'display_name' => $faker->name,
            'email'        => $faker->unique()->safeEmail,
            //'email_verified_at' => now(),
            'username'             => $faker->userName,
            'auto_attach_programs' => 1,
            'password'             => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token'       => Str::random(10),
            'address'              => $faker->address,
            'address2'             => $faker->address,
            'city'                 => $faker->city,
            'state'                => $faker->city,
            'zip'                  => $faker->randomNumber(5),
            'status'               => 'Active',
            'access_disabled'      => 0,
        ]);

        $user->patientInfo()->create([
            'user_id'         => $user->id,
            'birth_date'      => $faker->date('y-m-d'),
            'general_comment' => $faker->text,
        ]);

        return $user;
    }

    public function getQuestionWithOrder($instance, $order, $subOrder = null)
    {
        return $instance->questions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();
    }

    public function hraAnswerData(): Collection
    {
        return collect([
            [
                'order'    => 1,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Asian',
                ],
            ],
            [
                'order'    => 2,
                'subOrder' => null,
                'answer'   => [
                    'value' => 19,
                ],
            ],
            [
                'order'    => 3,
                'subOrder' => null,
                'answer'   => [
                    'feet'   => 5,
                    'inches' => 10,
                ],
            ],
            [
                'order'    => 4,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Female',
                ],
            ],
            [
                'order'    => 5,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Fair',
                ],
            ],
            [
                'order'    => 6,
                'subOrder' => null,
                'answer'   => [
                    'value' => '1-2',
                ],
            ],
            [
                'order'    => 7,
                'subOrder' => null,
                'answer'   => [
                    'value' => '3-4',
                ],
            ],
            [
                'order'    => 8,
                'subOrder' => null,
                'answer'   => [
                    'value' => '4+',
                ],
            ],
            [
                'order'    => 9,
                'subOrder' => null,
                'answer'   => [
                    'value' => '0',
                ],
            ],
            [
                'order'    => 10,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 11,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 11,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => 'This Year',
                ],
            ],
            [
                'order'    => 11,
                'subOrder' => 'b',
                'answer'   => [
                    'value' => 'This Year',
                ],
            ],
            [
                'order'    => 11,
                'subOrder' => 'c',
                'answer'   => [
                    'value' => '1/2',
                ],
            ],
            [
                'order'    => 11,
                'subOrder' => 'd',
                'answer'   => [
                    'value' => 'Maybe',
                ],
            ],
            [
                'order'    => 12,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 12,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => '<7 drinks per week',
                ],
            ],
            [
                'order'    => 13,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 13,
                'subOrder' => 'a',
                'answer'   => [
                    [
                        'name'      => 'Cannabis',
                        'frequency' => '76',
                    ],
                    [
                        'name'      => 'Iowaska',
                        'frequency' => '1',
                    ],
                ],
            ],
            [
                'order'    => 14,
                'subOrder' => null,
                'answer'   => [
                    'value' => '<3 times a week',
                ],
            ],
            [
                'order'    => 15,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 15,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 15,
                'subOrder' => 'b',
                'answer'   => [
                    'value' => 'Sometimes',
                ],
            ],
            [
                'order'    => 16,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'Cancer',
                        'type' => 'Colon',
                    ],
                    [
                        'name' => 'Depression',
                        'type' => null,
                    ],
                    [
                        'name' => 'Hepatitis',
                        'type' => null,
                    ],
                ],
            ],
            [
                'order'    => 17,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'Tangyness',
                    ],
                    [
                        'name' => 'Arch-tangyness',
                    ],
                ],
            ],
            [
                'order'    => 18,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'Colorectal Cancer',
                    ],
                    [
                        'name' => 'Depression',
                    ],
                ],
            ],
            [
                'order'    => 18,
                'subOrder' => 'a',
                'answer'   => [
                    [
                        'name'   => 'Colorectal Cancer',
                        'family' => [
                            'Mother',
                            'Father',
                        ],
                    ],
                    [
                        'name'   => 'Depression',
                        'family' => [
                            'Child',
                        ],
                    ],
                ],
            ],
            [
                'order'    => 19,
                'subOrder' => null,
                'answer'   => [
                    [
                        'reason'   => 'Appendix removal',
                        'location' => 'Somewhere',
                        'year'     => '1999',
                    ],
                    [
                        'reason'   => 'Heart Surgery',
                        'location' => 'Somewhere',
                        'year'     => '2001',
                    ],
                ],
            ],
            [
                'order'    => 20,
                'subOrder' => null,
                'answer'   => [
                    [
                        'drug'      => 'Tangecil',
                        'dose'      => '2',
                        'frequency' => 'daily',
                    ],
                    [
                        'drug'      => 'Tanjax 500 mg',
                        'dose'      => '1',
                        'frequency' => 'weekly',
                    ],
                ],
            ],
            [
                'order'    => 21,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'peanuts',
                    ],
                    [
                        'name' => 'figs',
                    ],
                ],
            ],
            [
                'order'    => 22,
                'subOrder' => '1',
                'answer'   => [
                    'value' => 'Several days',
                ],
            ],
            [
                'order'    => 22,
                'subOrder' => '2',
                'answer'   => [
                    'value' => 'Nearly Every Day',
                ],
            ],
            [
                'order'    => 23,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'Bathing',
                    ],
                    [
                        'name' => 'Preparing a meal',
                    ],
                ],
            ],
            [
                'order'    => 23,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => 'No',
                ],
            ],
            [
                'order'    => 24,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'No',
                ],
            ],
            [
                'order'    => 25,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 26,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'No',
                ],
            ],
            [
                'order'    => 27,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 28,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'No',
                ],
            ],
            [
                'order'    => 29,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Unsure',
                ],
            ],
            [
                'order'    => 30,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 31,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 32,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 33,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'No',
                ],
            ],
            [
                'order'    => 34,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Unsure',
                ],
            ],
            [
                'order'    => 35,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last 2-3 years',
                ],
            ],
            [
                'order'    => 36,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last 6-10 years',
                ],
            ],
            [
                'order'    => 37,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last year',
                ],
            ],
            [
                'order'    => 38,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last 6-10 years',
                ],
            ],
            [
                'order'    => 39,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last 6-10 years',
                ],
            ],
            [
                'order'    => 40,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last year',
                ],
            ],
            [
                'order'    => 41,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last 6-10 years',
                ],
            ],
            [
                'order'    => 42,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'In the last year',
                ],
            ],
            [
                'order'    => 43,
                'subOrder' => null,
                'answer'   => [
                    [
                        'provider_name' => 'Jonh Doe',
                        'specialty'     => 'Endocrinologist',
                        'location'      => 'Demo',
                        'phone_number'  => '123123123',
                    ],
                    [
                        'provider_name' => 'Jane Doe',
                        'specialty'     => 'Dermatologist',
                        'location'      => 'Demo',
                        'phone_number'  => '123123124',
                    ],
                ],
            ],
            [
                'order'    => 44,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 45,
                'subOrder' => null,
                'answer'   => [
                    'value' => 'Yes',
                ],
            ],
            [
                'order'    => 45,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => 'Unsure',
                ],
            ],
            [
                'order'    => 46,
                'subOrder' => null,
                'answer'   => [
                    [
                        'name' => 'I have a question about something',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Run the database seeds.
     *
     * This seeder is currently used for testing.
     * When the seeder runs it is going to create answers.
     * SurveyService will automatically update survey_status, and eventually set both instance status to complete,
     * That will trigger the generation of the 2 reports by Jobs/GeneratePatientReportsJob
     *
     * @return void
     */
    public function run()
    {
        $date    = Carbon::now();
        $service = new SurveyService();

        $user = User::ofType('participant')
            ->first();

        if ( ! $user) {
            $user = $this->createTestUser();
        }

        echo $user->id;

        $hraSurvey = Survey::with([
            'instances' => function ($instance) use ($date) {
                $instance->with('questions')
                    ->forYear($date->year);
            },
        ])
            ->where('name', Survey::HRA)->first();

        $vitalsSurvey = Survey::with([
            'instances' => function ($instance) use ($date) {
                $instance->with('questions')
                    ->forYear($date->year);
            },
        ])
            ->where('name', Survey::VITALS)->first();
        $hraInstance    = $hraSurvey->instances->first();
        $vitalsInstance = $vitalsSurvey->instances->first();

        $user->surveys()->attach(
            $hraSurvey->id,
            [
                'survey_instance_id' => $hraInstance->id,
                'status'             => SurveyInstance::PENDING,
            ]
        );

        $user->surveys()->attach(
            $vitalsSurvey->id,
            [
                'survey_instance_id' => $vitalsInstance->id,
                'status'             => SurveyInstance::PENDING,
            ]
        );

        $hraAnswers = $this->hraAnswerData();

        foreach ($hraAnswers as $answerData) {
            $question = $this->getQuestionWithOrder($hraInstance, $answerData['order'], $answerData['subOrder']);

            $service->updateOrCreateAnswer([
                'user_id'            => $user->id,
                'survey_instance_id' => $hraInstance->id,
                'question_id'        => $question->id,
                'value'              => $answerData['answer'],
            ]);
        }

        $vitalsAnswers = $this->vitalsAnswerData();

        foreach ($vitalsAnswers as $answerData) {
            $question = $this->getQuestionWithOrder($vitalsInstance, $answerData['order'], $answerData['subOrder']);

            $input = [
                'user_id'            => $user->id,
                'survey_instance_id' => $vitalsInstance->id,
                'question_id'        => $question->id,
                'value'              => $answerData['answer'],
            ];

            $service->updateOrCreateAnswer($input);
            //fix to generate reports
//            $service->updateSurveyInstanceStatus($input, true);
        }
    }

    public function vitalsAnswerData(): Collection
    {
        return collect([
            [
                'order'    => 1,
                'subOrder' => null,
                'answer'   => [
                    'first_metric'  => 140,
                    'second_metric' => 80,
                ],
            ],
            [
                'order'    => 2,
                'subOrder' => null,
                'answer'   => [
                    'value' => '150',
                ],
            ],
            [
                'order'    => 3,
                'subOrder' => null,
                'answer'   => [
                    'feet'   => 5,
                    'inches' => 10,
                ],
            ],
            [
                'order'    => 4,
                'subOrder' => null,
                'answer'   => [
                    'value' => '25',
                ],
            ],
            [
                'order'    => 5,
                'subOrder' => 'a',
                'answer'   => [
                    'value' => 3,
                ],
            ],
            [
                'order'    => 5,
                'subOrder' => 'b',
                'answer'   => [
                    'value' => 2,
                ],
            ],
            [
                'order'    => 5,
                'subOrder' => 'c',
                'answer'   => [
                    'value' => 5,
                ],
            ],
        ]);
    }
}
