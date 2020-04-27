<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\CarePlanWasQAApproved;
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

    public static function assignToStandByNurse(User $patient)
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

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        self::assignToStandByNurse($event->patient);
    }
}
