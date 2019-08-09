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
        $q14 = \App\Question::where('body', '=', 'How often do you exerise?')
            ->first();

        $q5a = \App\Question::where('body', '=', 'Word Recall (1 point for each word spontaneously recalled without cueing)')
            ->first();

        if ($q14) {
            $q14->body = 'How often do you exercise?';
            $q14->save();
        }

        if ($q5a) {
            $q5a->body = 'Word Recall (1 point for each word spontaneously recalled without cueing) 
                http://mini-cog.com/wp-content/uploads/2015/12/Universal-Mini-Cog-Form-011916.pdf';
            $q5a->save();
        }
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
