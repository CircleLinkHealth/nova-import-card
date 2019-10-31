<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskRecommendationsRemoveColonPunctuationMark extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'ppp_task_recommendations';

        DB::table($table)
          ->where('title', "Cognitive Impairment:")
          ->update(['title' => "Cognitive Impairment"]);

        DB::table($table)
          ->where('title', "Immunizations/Vaccines:")
          ->update(['title' => "Immunizations/Vaccines"]);

        DB::table($table)
          ->where('title', "Screenings:")
          ->update(['title' => "Screenings"]);

        DB::table($table)
          ->where('title', "Other misc:")
          ->update(['title' => "Other misc"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = 'ppp_task_recommendations';

        DB::table($table)
          ->where('title', "Cognitive Impairment")
          ->update(['title' => "Cognitive Impairment:"]);

        DB::table($table)
          ->where('title', "Immunizations/Vaccines")
          ->update(['title' => "Immunizations/Vaccines:"]);

        DB::table($table)
          ->where('title', "Screenings")
          ->update(['title' => "Screenings:"]);

        DB::table($table)
          ->where('title', "Other misc")
          ->update(['title' => "Other misc:"]);
    }
}
