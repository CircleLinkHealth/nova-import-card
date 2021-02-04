<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class CreateCameronEnrolmentLetter extends Migration
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
        if (class_exists('CircleLinkHealth\Eligibility\Database\Seeders\GenerateCameronLetter')) {
            Artisan::call('create:cameronLetter');
        }
    }
}
