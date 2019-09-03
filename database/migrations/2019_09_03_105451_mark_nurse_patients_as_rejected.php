<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Migrations\Migration;

class MarkNursePatientsAsRejected extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $month = Carbon::parse('2019-08-01')->startOfMonth();

        $patientIds = User::ofType('participant')
            ->whereHas('inboundCalls', function ($calls) use ($month) {
                              $calls->whereHas('outboundUser', function ($user) use ($month) {
                                  $user->ofType('care-center')
                                      ->where('first_name', '=', 'Rachel')
                                      ->where('last_name', '=', 'Walker');
                              })
                                  ->ofMonth($month)
                                  ->where('status', '=', 'reached');
                          })
            ->pluck('id')
            ->all();

        PatientMonthlySummary::getForMonth($month)
            ->whereIn('patient_id', $patientIds)
            ->update([
                'approved' => false,
                'rejected' => true,
            ]);
    }
}
