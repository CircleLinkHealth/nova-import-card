<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddConditionsToHraQ353639 extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('surveys')->updateOrInsert(
            ['id' => 1],
            [
                'name'        => 'AWV Test Survey',
                'description' => 'Added to avoid having null in non nullable questions.survey_id',
            ]
        );

        $table                        = 'questions';
        $dataConditionsForCase35And36 = json_encode(
            [
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Female',
                ],
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Transgender',
                ],
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Other',
                ],
            ]
        );

        $dataConditionsForCase39 = json_encode(
            [
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Male',
                ],
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Transgender',
                ],
                [
                    'related_question_order_number'    => 4,
                    'related_question_expected_answer' => 'Other',
                ],
            ]
        );

        $dataConditionsForCase42 = json_encode(
            [
                [
                    'operator'                         => 'greater_than',
                    'related_question_order_number'    => 2,
                    'related_question_expected_answer' => '44',
                ],
            ]
        );

        // $hraQuestionOrder35
        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had a Breast Cancer Screening (Mammogram)?'],
            [
                'conditions' => $dataConditionsForCase35And36,
                'survey_id'  => 1,
                'optional'   => true,
            ]
        );

        // $hraQuestionOrder36
        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had a Cervical cancer Screening (Pap Smear)?'],
            [
                'conditions' => $dataConditionsForCase35And36,
                'survey_id'  => 1,
                'optional'   => true,
            ]
        );

        //$hraQuestionOrder39
        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had a Prostate Cancer Screening (Prostate specific antigen (PSA))?'],
            [
                'conditions' => $dataConditionsForCase39,
                'survey_id'  => 1,
                'optional'   => true,
            ]
        );

        //$hraQuestionOrder42 - cpm-1447
        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had an Intimate Partner Violence/Domestic Violence Screening?'],
            [
                'conditions' => $dataConditionsForCase42,
                'survey_id'  => 1,
                'optional'   => true,
            ]
        );

        $dataConditionsForQ32 = json_encode(
            [
                [
                    'operator'                         => 'greater_or_equal_than',
                    'related_question_order_number'    => 2,
                    'related_question_expected_answer' => 26,
                ],
            ]
        );

        $dataConditionsForQ42 = json_encode(
            [
                [
                    'operator'                         => 'greater_or_equal_than',
                    'related_question_order_number'    => 2,
                    'related_question_expected_answer' => 44,
                ],
            ]
        );

        DB::table($table)->updateOrInsert(
            ['body' => 'Have you received 2 doses of Human Papillomavirus (HPV) Vaccination before age 15 OR 3 doses between ages 15 and 26?'],
            [
                'conditions' => $dataConditionsForQ32,
                'survey_id'  => 1,
                'optional'   => 1,
            ]
        );

        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had an Intimate Partner Violence/Domestic Violence Screening?'],
            [
                'conditions' => $dataConditionsForQ42,
                'survey_id'  => 1,
                'optional'   => 1,
            ]
        );
    }
}
