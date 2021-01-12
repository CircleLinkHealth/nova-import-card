<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Database\Seeders;

use Carbon;
use DB;
use Illuminate\Database\Seeder;

class CreateEnrolleesSurveySeeder extends Seeder
{
    const ADDRESS          = 'address';
    const CHECKBOX         = 'checkbox';
    const CONFIRM_ADDRESS  = 'Q_CONFIRM_ADDRESS';
    const CONFIRM_EMAIL    = 'Q_CONFIRM_EMAIL';
    const CONFIRM_LETTER   = 'Q_CONFIRM_LETTER';
    const CONFIRMATION     = 'confirmation';
    const DATE             = 'date';
    const DOB              = 'Q_DOB';
    const NUMBER           = 'number';
    const PHONE            = 'phone';
    const PREFERRED_DAYS   = 'Q_PREFERRED_DAYS';
    const PREFERRED_NUMBER = 'Q_PREFERRED_NUMBER';
    const PREFERRED_TIME   = 'Q_PREFERRED_TIME';
//    const QUESTION_TYPE_DOB = 'dob';
    const REQUESTS_INFO = 'Q_REQUESTS_INFO';
    const SELECT        = 'select';
    const TEXT          = 'text';
    const TIME          = 'time';

    public function createQuestions($instance, $questionsData)
    {
        $questionsTable = 'questions';
        foreach ($questionsData as $questionData) {
            if (array_key_exists('question_group', $questionData)) {
                $groupId = DB::table('question_groups')->insertGetId(
                    [
                        'body' => $questionData['question_group'],
                    ]
                );
            } else {
                $groupId = null;
            }

            $questionId = DB::table($questionsTable)->insertGetId([
                'identifier'        => $questionData['identifier'],
                'survey_id'         => $instance->survey_id,
                'body'              => $questionData['question_body'],
                'question_group_id' => $groupId,
                'optional'          => array_key_exists('optional', $questionData)
                    ? $questionData['optional']
                    : false,
                'conditions' => array_key_exists('conditions', $questionData)
                    ? json_encode($questionData['conditions'])
                    : null,
            ]);

            $selfId = DB::table('question_types')->insertGetId([
                'type'        => $questionData['question_type'],
                'question_id' => $questionId,
            ]);

            if (array_key_exists('question_type_answers', $questionData)) {
                foreach ($questionData['question_type_answers'] as $selfAnswer) {
                    DB::table('question_types_answers')->insert(
                        [
                            'question_type_id' => ! empty($selfAnswer)
                                ? $selfId
                                : null,
                            'value' => array_key_exists('type_answer_body', $selfAnswer)
                                ? $selfAnswer['type_answer_body']
                                : null,
                            'options' => array_key_exists('options', $selfAnswer)
                                ? json_encode($selfAnswer['options'])
                                : null,
                        ]
                    );
                }
            }

            DB::table('survey_questions')->where('survey_instance_id', '=', $instance->id)
                ->where('question_id', '=', $questionId)
                ->updateOrInsert(
                    [
                        'survey_instance_id' => $instance->id,
                        'question_id'        => $questionId,
                        'order'              => $questionData['order'],
                        'sub_order'          => array_key_exists('sub_order', $questionData)
                            ? $questionData['sub_order']
                            : null,
                    ]
                );
        }
    }

    public function enrolleesQuestionData()
    {
        return collect([
            [
                'identifier'            => self::CONFIRM_EMAIL,
                'order'                 => 1,
                'question_body'         => 'Please confirm or update your email address:',
                'question_type'         => self::ADDRESS,
                'question_type_answers' => [
                    [
                        'options' => [
                            'placeholder' => 'Known email if exists',
                        ],
                    ],
                ],
            ],
            [
                'identifier'            => self::PREFERRED_NUMBER,
                'order'                 => 2,
                'question_body'         => 'Preferred phone number for nurse to call',
                'question_type'         => self::PHONE,
                'question_type_answers' => [
                    [
                        'options' => [
                            'input_format' => 'phone',
                        ],
                    ],
                ],
            ],
            [
                'identifier'            => self::PREFERRED_DAYS,
                'order'                 => 3,
                'sub_order'             => 'a',
                'question_group'        => 'Please choose preferred days and time to contact:',
                'question_body'         => 'Choose preferred contact days:',
                'question_type'         => self::CHECKBOX,
                'question_type_answers' => [
                    ['type_answer_body' => 'Monday'],
                    ['type_answer_body' => 'Tuesday'],
                    ['type_answer_body' => 'Wednesday'],
                    ['type_answer_body' => 'Thursday'],
                    ['type_answer_body' => 'Friday'],
                ],
            ],
            [
                'identifier'            => self::PREFERRED_TIME,
                'order'                 => 3,
                'sub_order'             => 'b',
                'question_group'        => 'Please choose preferred days and time to contact:',
                'question_body'         => 'Choose preferred contact time:',
                'question_type'         => self::CHECKBOX,
                'question_type_answers' => [
                    ['type_answer_body' => '9am - 12pm'],
                    ['type_answer_body' => '12pm - 3pm '],
                    ['type_answer_body' => '3pm - 6pm'],
                ],
            ],

            [
                'identifier'            => self::REQUESTS_INFO,
                'order'                 => 4,
                'question_body'         => 'Anything you would like your nurse to know:',
                'optional'              => true,
                'question_type'         => self::TEXT,
                'question_type_answers' => [
                    [
                        'options' => [
                            'placeholder' => 'Type response here...',
                        ],
                    ],
                ],
            ],

            [
                'identifier'            => self::CONFIRM_ADDRESS,
                'order'                 => 5,
                'question_body'         => 'Please confirm or update your address:',
                'question_type'         => self::ADDRESS,
                'question_type_answers' => [
                    [
                        'options' => [
                            'placeholder' => 'Known addrees if exists',
                        ],
                    ],
                ],
            ],
            //            [
            //                'identifier'    => self::DOB,
            //                'order'         => 6,
            //                'question_body' => 'Please update or confirm your date of birth',
            //                'question_type' => self::DOB,
            //                'conditions'    => [
            //                    'is_auto_generated' => true,
            //                    'generated_from'    => [
            //                        [
            //                            'key' => 'dob',
            //                        ],
            //                    ],
            //                ],
            //            ],
            [
                'identifier'    => self::CONFIRM_LETTER,
                'order'         => 6,
                'optional'      => true,
                'question_body' => 'Please confirm you have read the letter',
                'question_type' => self::CONFIRMATION,
                'conditions'    => [
                    [
                        'nonAwvCheck' => 'isSurveyOnlyUser',
                    ],
                ],
                'question_type_answers' => [
                    [
                        'type_answer_body' => 'Confirm',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time                 = Carbon::now();
        $surveyInstancesTable = 'survey_instances';
        $surveysTable         = 'surveys';
        $enrolleeSurveyExists = DB::table($surveysTable)
            ->where('name', '=', 'Enrollees')
            ->exists();

        if ( ! $enrolleeSurveyExists) {
            $enrolleesSurveyId = DB::table($surveysTable)->insertGetId(
                [
                    'name'        => 'Enrollees',
                    'description' => 'Enrollees Survey',
                ]
            );

            DB::table($surveyInstancesTable)->insert(
                [
                    'survey_id'  => $enrolleesSurveyId,
                    'year'       => $time->year,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]
            );

            $currentInstance = DB::table($surveyInstancesTable)->where('survey_id', '=', $enrolleesSurveyId)->first();

            $questionsData = $this->enrolleesQuestionData();
            $this->createQuestions($currentInstance, $questionsData);
        }
    }
}
