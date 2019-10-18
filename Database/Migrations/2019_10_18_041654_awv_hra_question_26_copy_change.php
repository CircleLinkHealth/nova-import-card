<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AwvHraQuestion26CopyChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $questionsTable = "questions";

        DB::table($questionsTable)
          ->where('body', "Have you had a flu shot this year or are you planning to receive one this year?")
          ->update(['body' => "Have you had a flu shot this year or do you have serious plans to get one this year?"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $questionsTable = "questions";

        DB::table($questionsTable)
          ->where('body', "Have you had a flu shot this year or do you have serious plans to get one this year?")
          ->update(['body' => "Have you had a flu shot this year or are you planning to receive one this year?"]);
    }
}
