<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class MakeQuestions1920And21ToOptional extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $questionsTable = 'questions';

        DB::table($questionsTable)
            ->where('body', 'Please list any surgeries/hospital stays you have had and their approximate date/year:')
            ->update(['optional' => 0]);

        DB::table($questionsTable)
            ->where(
                'body',
                'If you are taking any medications regularly, please list them here, including over-the-counter pharmaceuticals:'
            )
            ->update(['optional' => 0]);

        DB::table($questionsTable)
            ->where('body', 'Please list any allergies or reactions:')
            ->update(['optional' => 0]);
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $questionsTable = 'questions';

        DB::table($questionsTable)
            ->where('body', 'Please list any surgeries/hospital stays you have had and their approximate date/year:')
            ->update(['optional' => 1]);

        DB::table($questionsTable)
            ->where(
                'body',
                'If you are taking any medications regularly, please list them here, including over-the-counter pharmaceuticals:'
            )
            ->update(['optional' => 1]);

        DB::table($questionsTable)
            ->where('body', 'Please list any allergies or reactions:')
            ->update(['optional' => 1]);
    }
}
