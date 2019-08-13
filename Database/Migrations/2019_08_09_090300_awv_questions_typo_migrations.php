<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AwvQuestionsTypoMigrations extends Migration
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
          ->where('body', 'How often do you exerise?')
          ->update(['body' => 'How often do you exercise?']);

        DB::table($questionsTable)
          ->where('body', 'Word Recall (1 point for each word spontaneously recalled without cueing)')
          ->update(['body' => 'Word Recall (1 point for each word spontaneously recalled without cueing) 
                http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
