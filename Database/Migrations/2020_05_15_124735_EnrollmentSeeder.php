<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\Database\Seeders\GenerateCommonwealthPainAssociatesPllcLetter;
use Illuminate\Database\Migrations\Migration;

class EnrollmentSeeder extends Migration
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
        if ( ! isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => GenerateCommonwealthPainAssociatesPllcLetter::class,
            ]);
        }
    }
}
