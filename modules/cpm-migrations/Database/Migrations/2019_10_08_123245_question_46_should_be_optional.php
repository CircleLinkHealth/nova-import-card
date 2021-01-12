<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class Question46ShouldBeOptional extends Migration
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
        $table = 'questions';
        DB::table($table)
            ->where('body', '=', 'Do you have any other questions or concerns that you would like to speak to your provider about at your next Annual Wellness Visit?')
            ->update([
                'optional' => true,
            ]);
    }
}
