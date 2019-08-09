<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class CreateFixTypoInAwvQuestionsMorganTable extends Migration
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
}
