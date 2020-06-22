<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Database\Seeders\CreateEnrolleesSurveySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEnrolleesSurvey extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => CreateEnrolleesSurveySeeder::class,
            ]);
        }
    }
}
