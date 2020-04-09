<?php

use CircleLinkHealth\Eligibility\Database\Seeders\EnrollmentInvitationLetterSeeder;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
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
        $enrollmentInvitationLetter = EnrollmentInvitationLetter::first();
//        run only if table is empty
        if (!isUnitTestingEnv() && empty($enrollmentInvitationLetter)) {
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
