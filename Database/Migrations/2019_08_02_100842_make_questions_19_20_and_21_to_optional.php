<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeQuestions1920And21ToOptional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $q19 = \App\Question::where('body', '=', 'Please list any surgeries/hospital stays you have had and their approximate date/year:')
                            ->first();

        if ($q19) {
            $q19->optional = 1;
            $q19->save();
        }

        $q20 = \App\Question::where('body', '=', 'If you are taking any medications regularly, please list them here, including over-the-counter pharmaceuticals:')
                            ->first();

        if ($q20) {
            $q20->optional = 1;
            $q20->save();
        }

        $q21 = \App\Question::where('body', '=', 'Please list any allergies or reactions:')
                            ->first();

        if ($q21) {
            $q21->optional = 1;
            $q21->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $q19 = \App\Question::where('body', '=', 'Please list any surgeries/hospital stays you have had and their approximate date/year:')
                            ->first();

        if ($q19) {
            $q19->optional = 0;
            $q19->save();
        }

        $q20 = \App\Question::where('body', '=', 'If you are taking any medications regularly, please list them here, including over-the-counter pharmaceuticals:')
                            ->first();

        if ($q20) {
            $q20->optional = 0;
            $q20->save();
        }

        $q21 = \App\Question::where('body', '=', 'Please list any allergies or reactions:')
                            ->first();

        if ($q21) {
            $q21->optional = 0;
            $q21->save();
        }
    }
}
