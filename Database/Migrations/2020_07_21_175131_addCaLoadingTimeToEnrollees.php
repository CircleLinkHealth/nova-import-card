<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaLoadingTimeToEnrollees extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //get CAs with assigned enrollees where they have been called
        //get all ca page timers where they don't have enrollee id

        //loop through activities taking enrollee by count index -> once max is reached do i - enrolleeCount
    }
}
