<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AwvVitalsQuestion4CopyChange extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questionsTable = 'questions';

        DB::table($questionsTable)
            ->where('body', "Based on your inputs, patient's body mass index (BMI) is the following:")
            ->update(['body' => "What is the patient's body mass index (BMI)?"]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionsTable = 'questions';

        DB::table($questionsTable)
            ->where('body', "What is the patient's body mass index (BMI)?")
            ->update(['body' => "Based on your inputs, patient's body mass index (BMI) is the following:"]);
    }
}
