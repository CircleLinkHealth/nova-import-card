<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddConditionsToQuestions3244Table extends Migration
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
        if (isUnitTestingEnv()) {
            return;
        }
        $table = 'questions';

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
            ]
        );

        DB::table($table)->updateOrInsert(
            ['body' => 'When was the last time you had an Intimate Partner Violence/Domestic Violence Screening?'],
            [
                'conditions' => $dataConditionsForQ42,
            ]
        );
    }
}
