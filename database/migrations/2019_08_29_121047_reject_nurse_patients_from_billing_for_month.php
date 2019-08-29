<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Migrations\Migration;

class RejectNursePatientsFromBillingForMonth extends Migration
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
        $patientIds = User::ofType('participant')
            ->whereHas('inboundCalls', function ($calls) {
                $calls->whereHas('outboundUser', function ($user) {
                    $user->ofType('care-center')
                        ->where('first_name', '=', 'Rachel')
                        ->where('last_name', '=', 'Walker');
                })
                    ->ofMonth(Carbon::now()->startOfMonth())
                    ->where('status', '=', 'reached');
            })
            ->pluck('id')
            ->all();

        PatientMonthlySummary::getCurrent()
            ->whereIn('patient_id', $patientIds)
            ->update([
                'approved' => false,
                'rejected' => true,
            ]);
    }
}
