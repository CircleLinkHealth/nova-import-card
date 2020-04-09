<?php

use CircleLinkHealth\Eligibility\Database\Seeders\EnrollmentInvitationLetterSeeder;
use Illuminate\Database\Migrations\Migration;

class PopulateEnrollmentLetter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!isUnitTestingEnv()) {
            Artisan::call('db:seed', [
                '--class' => EnrollmentInvitationLetterSeeder::class,
            ]);
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
