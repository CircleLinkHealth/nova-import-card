<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Database\Seeders\GenerateCommonwealthPainAssociatesPllcLetter;
use Illuminate\Database\Migrations\Migration;

class PopulateEnrollmentLetterV2 extends Migration
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
        if ( ! isUnitTestingEnv() && isCpm()) {
            Artisan::call('db:seed', [
                '--class' => GenerateCommonwealthPainAssociatesPllcLetter::class,
            ]);
        }
    }
}