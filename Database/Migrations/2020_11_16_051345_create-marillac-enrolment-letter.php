<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class CreateMarillacEnrolmentLetter extends Migration
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
        //test
        Artisan::call('db:seed', ['--class' => 'CircleLinkHealth\Eligibility\Database\Seeders\GenerateMarillacHealthLetter']);
    }
}
