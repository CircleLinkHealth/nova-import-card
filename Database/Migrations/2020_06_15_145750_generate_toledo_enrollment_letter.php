<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class GenerateToledoEnrollmentLetter extends Migration
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
        if ( ! \Illuminate\Support\Facades\App::environment(['testing', 'review'])) {
            Artisan::call('db:seed', ['--class' => 'CircleLinkHealth\Eligibility\Database\Seeders\GenerateToledoClinicLetter']);
        }
    }
}
