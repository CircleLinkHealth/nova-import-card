<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddConstantSignatoryNamesToLetters extends Migration
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
        /*
         * this command does not exist anymore.
        if ( ! isUnitTestingEnv() || ! App::environment('review')) {
            Artisan::call('update:enrolmentLettersSignatoryName');
        }
        */
    }
}
