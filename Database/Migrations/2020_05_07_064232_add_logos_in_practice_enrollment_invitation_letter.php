<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddLogosInPracticeEnrollmentInvitationLetter extends Migration
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
        $practice = DB::table('practices')
            ->where('name', '=', 'commonwealth-pain-associates-pllc')
            ->first();

        if ($practice) {
            DB::table('enrollment_invitation_letters')
                ->where('practice_id', '=', $practice->id)
                ->update([
                    'practice_logo_src'      => '/img/logos/CommonWealth/commonwealth_logo.png',
                    'customer_signature_src' => '/img/logos/CommonWealth/commonwealth_signature.jpg',
                ]);
        }

        $demo = DB::table('practices')
            ->where('name', '=', 'demo')
            ->first();

        if ($demo) {
            DB::table('enrollment_invitation_letters')
                ->where('practice_id', '=', $demo->id)
                ->update([
                    'practice_logo_src'      => '/img/logos/CommonWealth/commonwealth_logo.png',
                    'customer_signature_src' => '/img/logos/CommonWealth/commonwealth_signature.jpg',
                ]);
        }
    }
}
