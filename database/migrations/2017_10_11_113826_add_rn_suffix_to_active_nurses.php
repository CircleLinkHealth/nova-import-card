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
        foreach (Nurse::whereStatus('active')->get() as $nurse) {
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
