<?php

use App\Nurse;
use Illuminate\Database\Migrations\Migration;

class AddRnSuffixToActiveNurses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $nurses = Nurse::whereHas('user', function ($query) {
                $query->whereNotIn('display_name', [
                    'Nivea Taylor',
                    'Jared Palakanis',
                    'Dawn Brook',
                    'Davona McCready',
                ]);
        })
            ->get();

        foreach ($nurses as $nurse) {
            if (!$nurse->user) {
                continue;
            }

            $nurse->user->suffix = 'RN';
            $nurse->user->save();
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
