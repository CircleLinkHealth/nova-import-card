<?php

use App\Services\SurveyService;
use App\Survey;
use App\SurveyInstance;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PatientSurveyAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker   = Factory::create();
        $date    = Carbon::now();
        $service = new SurveyService();

        $user = User::create([
            'first_name'        => $faker->name,
            'last_name'         => $faker->name,
            'display_name'      => $faker->name,
            'email'             => $faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password'          => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'remember_token'    => str_random(10),
        ]);

        $user->patientInfo()->create([
            'birth_date' => $faker->date('y-m-d'),
        ]);

        $hraSurvey = Survey::with([
            'instances' => function ($instance) use ($date) {
                $instance->with('questions')
                         ->where('start_date', $date->copy()->startOfYear()->toDateString())
                         ->where('end_Date', $date->copy()->endOfYear()->toDateString());
            },
        ])
                           ->where('name', Survey::HRA)->first();

        $vitalsSurvey   = Survey::with([
            'instances' => function ($instance) use ($date) {
                $instance->with('questions')
                         ->where('start_date', $date->copy()->startOfYear()->toDateString())
                         ->where('end_Date', $date->copy()->endOfYear()->toDateString());
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
                'value_1'            => $answerData['answer'],
            ]);
        }

        $vitalsAnswers = $this->vitalsAnswerData();

        foreach ($vitalsAnswers as $answerData) {

            $question = $this->getQuestionWithOrder($vitalsInstance, $answerData['order'], $answerData['subOrder']);

            $service->updateOrCreateAnswer([
                'user_id'            => $user->id,
                'survey_instance_id' => $vitalsInstance->id,
                'question_id'        => $question->id,
                'value_1'            => $answerData['answer'],
            ]);
        }
    }

    public function getQuestionWithOrder($instance, $order, $subOrder = null)
    {
        return $instance->questions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();


    }

    public function vitalsAnswerData(): Collection
    {
        return collect([
            [
                'order'    => 1,
                'subOrder' => null,
                'answer'   => json_encode([
                    'first_metric'  => 140,
                    'second_metric' => 80,
                ]),
            ],
            [
                'order'    => 2,
                'subOrder' => null,
                'answer'   => '150',
            ],
            [
                'order'    => 3,
                'subOrder' => null,
                'answer'   => json_encode([
                    'feet'   => 5,
                    'inches' => 10,
                ]),
            ],
            [
                'order'    => 4,
                'subOrder' => null,
                'answer'   => '25',
            ],
            [
                'order'    => 5,
                'subOrder' => 'a',
                'answer'   => 3,
            ],
            [
                'order'    => 5,
                'subOrder' => 'b',
                'answer'   => 2,
            ],
            [
                'order'    => 5,
                'subOrder' => 'c',
                'answer'   => 5,
            ],
        ]);
    }

    public function hraAnswerData(): Collection
    {
        return collect([
            [
                'order'    => 1,
                'subOrder' => null,
                'answer'   => 'Asian',
            ],
            [
                'order'    => 2,
                'subOrder' => null,
                'answer'   => 19,
            ],
            [
                'order'    => 3,
                'subOrder' => null,
                'answer'   => json_encode([
                    'feet'   => 5,
                    'inches' => 10,
                ]),
            ],
            [
                'order'    => 4,
                'subOrder' => null,
                'answer'   => 'Female',
            ],
            [
                'order'    => 5,
                'subOrder' => null,
                'answer'   => 'Fair',
            ],
            [
                'order'    => 6,
                'subOrder' => null,
                'answer'   => '1-2',
            ],
            [
                'order'    => 7,
                'subOrder' => null,
                'answer'   => '3-4',
            ],
            [
                'order'    => 8,
                'subOrder' => null,
                'answer'   => '4+',
            ],
            [
                'order'    => 9,
                'subOrder' => null,
                'answer'   => '0',
            ],
            [
                'order'    => 10,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 11,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 11,
                'subOrder' => 'a',
                'answer'   => 'This Year',
            ],
            [
                'order'    => 11,
                'subOrder' => 'b',
                'answer'   => 'This Year',
            ],
            [
                'order'    => 11,
                'subOrder' => 'c',
                'answer'   => '1/2',
            ],
            [
                'order'    => 11,
                'subOrder' => 'd',
                'answer'   => 'Maybe',
            ],
            [
                'order'    => 12,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 12,
                'subOrder' => 'a',
                'answer'   => '<7 drinks per week',
            ],
            [
                'order'    => 13,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 13,
                'subOrder' => 'a',
                'answer'   => json_encode([
                    [
                        'name'      => 'Cannabis',
                        'frequency' => '76',
                    ],
                    [
                        'name'      => 'Iowaska',
                        'frequency' => '1',
                    ],
                ]),
            ],
            [
                'order'    => 14,
                'subOrder' => null,
                'answer'   => '<3 times a week',
            ],
            [
                'order'    => 15,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 15,
                'subOrder' => 'a',
                'answer'   => 'Yes',
            ],
            [
                'order'    => 15,
                'subOrder' => 'b',
                'answer'   => 'Sometimes',
                'answer'   => 'Sometimes',
            ],
            [
                'order'    => 16,
                'subOrder' => null,
                'answer'   => json_encode([
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
                ]),
            ],
            [
                'order'    => 17,
                'subOrder' => null,
                'answer'   => json_encode([
                    [
                        'name' => 'Tangyness',

                    ],
                    [
                        'name' => 'Arch-tangyness',
                    ],
                ]),
            ],
            [
                'order'    => 18,
                'subOrder' => null,
                'answer'   => json_encode([
                    [
                        'name' => 'Colorectal Cancer',

                    ],
                    [
                        'name' => 'Depression',
                    ],
                ]),
            ],
            [
                'order'    => 18,
                'subOrder' => 'a',
                'answer'   => json_encode([
                    [
                        'name'   => 'Colorectal Cancer',
                        'family' => 'Mother',
                    ],
                    [
                        'name'   => 'Depression',
                        'family' => 'Child',
                    ],
                ]),
            ],
            [
                'order'    => 19,
                'subOrder' => null,
                'answer'   => json_encode([
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
                ]),
            ],
            [
                'order'    => 20,
                'subOrder' => null,
                'answer'   => json_encode([
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
                ]),
            ],
            [
                'order'    => 21,
                'subOrder' => null,
                'answer'   => json_encode([
                    [
                        'name' => 'peanuts',

                    ],
                    [
                        'name' => 'figs',
                    ],
                ]),
            ],
            [
                'order'    => 22,
                'subOrder' => '1',
                'answer'   => 'Several days',
            ],
            [
                'order'    => 22,
                'subOrder' => '2',
                'answer'   => 'Nearly every day',
            ],
            [
                'order'    => 23,
                'subOrder' => null,
                'answer'   => json_encode([
                    [
                        'name' => 'Bathing',

                    ],
                    [
                        'name' => 'Preparing a meal',
                    ],
                ]),
            ],
            [
                'order'    => 23,
                'subOrder' => 'a',
                'answer'   => 'No',
            ],
            [
                'order'    => 24,
                'subOrder' => null,
                'answer'   => 'No',
            ],
            [
                'order'    => 25,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 26,
                'subOrder' => null,
                'answer'   => 'No',
            ],
            [
                'order'    => 27,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 28,
                'subOrder' => null,
                'answer'   => 'No',
            ],
            [
                'order'    => 29,
                'subOrder' => null,
                'answer'   => 'Unsure',
            ],
            [
                'order'    => 30,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 31,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 32,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 33,
                'subOrder' => null,
                'answer'   => 'No',
            ],
            [
                'order'    => 34,
                'subOrder' => null,
                'answer'   => 'Unsure',
            ],
            [
                'order'    => 35,
                'subOrder' => null,
                'answer'   => 'In the last 2-3 years',
            ],
            [
                'order'    => 36,
                'subOrder' => null,
                'answer'   => 'In the last 6-10 years',
            ],
            [
                'order'    => 37,
                'subOrder' => null,
                'answer'   => 'In the last year',
            ],
            [
                'order'    => 38,
                'subOrder' => null,
                'answer'   => 'In the last  4-5 years',
            ],
            [
                'order'    => 39,
                'subOrder' => null,
                'answer'   => 'In the last 6-10 years',
            ],
            [
                'order'    => 40,
                'subOrder' => null,
                'answer'   => 'In the last year',
            ],
            [
                'order'    => 41,
                'subOrder' => null,
                'answer'   => 'In the last 6-10 years',
            ],
            [
                'order'    => 42,
                'subOrder' => null,
                'answer'   => 'In the last year',
            ],
            [
                'order'    => 43,
                'subOrder' => null,
                'answer'   => json_encode([
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
                ]),
            ],
            [
                'order'    => 44,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 45,
                'subOrder' => null,
                'answer'   => 'Yes',
            ],
            [
                'order'    => 45,
                'subOrder' => 'a',
                'answer'   => 'Unsure',
            ],
            [
                'order'    => 46,
                'subOrder' => null,
                'answer'   => 'I have a question about something',
            ],
        ]);
    }
}
