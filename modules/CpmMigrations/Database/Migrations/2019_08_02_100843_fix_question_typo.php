<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class FixQuestionTypo extends Migration
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
        $questionsTable = 'questions';

        DB::table($questionsTable)
            ->where('body', 'Clock Draw (Normal clock = 2 points. A normal clock has all numbers placed in the cor-rect sequence and approximately correct position (e.g., 12, 3, 6 and 9 are in anchor positions) with no missing or duplicate numbers. Hands are point-ing to the 11 and 2 (11:10). Hand length is not scored.Inability or refusal to draw a clock (abnormal) = 0 points.)')
            ->update(['body' => 'Clock Draw (Normal clock = 2 points. A normal clock has all numbers placed in the correct sequence and approximately correct position (e.g., 12, 3, 6 and 9 are in anchor positions) with no missing or duplicate numbers. Hands are pointing to the 11 and 2 (11:10). Hand length is not scored. Inability or refusal to draw a clock (abnormal) = 0 points.)']);
    }
}
