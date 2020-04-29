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
use Illuminate\Database\QueryException;

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

    public static function assignCallToStandByNurse(User $patient)
    {
        if ( ! $standByNurseId = StandByNurseUser::id()) {
            return null;
        }

        $scheduler = app()->make(SchedulerService::class);

        if ($scheduler->hasScheduledCall($patient)) {
            return null;
        }

        return $scheduler->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'system - patient status changed to enrolled', $standByNurseId);
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

        try {
            return PatientNurse::updateOrCreate(
                ['patient_user_id' => $patient->id],
                [
                    'nurse_user_id'           => $standByNurseId,
                    'temporary_nurse_user_id' => null,
                    'temporary_from'          => null,
                    'temporary_to'            => null,
                ]
            );
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? null;
            if (1062 != $errorCode) {
                throw $e;
            }

            \Log::error('Attempted to create duplicate PatientNurse for patientid:'.$patient->id);
        }
    }
}
