<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class ChangeQuestion26Text extends Migration
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

        DB::table($table)->where('body', '=', 'Have you had a flu shot this year or are you planning to receive one this year?')
            ->update(['body' => 'Have you had a flu shot this year or do you have serious plans to get one this year?']);
    }
}
