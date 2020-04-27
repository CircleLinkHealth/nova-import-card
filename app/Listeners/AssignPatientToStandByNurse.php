<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CarePlanWasQAApproved;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\User;

class AssignPatientToStandByNurse
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        self::makeStandByNursePrimary($event->patient);
        self::assignCallToStandByNurse($event->patient);
    }

    public static function makeStandByNursePrimary(User $patient)
    {
        if ( ! $standByNurseId = StandByNurseUser::id()) {
            return null;
        }

        return PatientNurse::updateOrCreate(
            ['patient_user_id' => $patient->id],
            [
                'patient_user_id'         => $patient->id,
                'nurse_user_id'           => $standByNurseId,
                'temporary_nurse_user_id' => null,
                'temporary_from'          => null,
                'temporary_to'            => null,
            ]
        );
    }

    private static function assignCallToStandByNurse(User $patient)
    {
        if ( ! $standByNurseId = StandByNurseUser::id()) {
            return null;
        }

        return (app(SchedulerService::class))->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'system - patient status changed to enrolled', $standByNurseId);
    }
}
