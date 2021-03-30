<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SelfEnrollment\Database\Seeders\GenerateCommonwealthPainAssociatesPllcLetter;
use Illuminate\Database\Migrations\Migration;

class PopulateEnrollmentLetter extends Migration
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
