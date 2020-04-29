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
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Database\QueryException;

class AssignPatientToStandByNurse
{
    public static function assign(User $patient)
    {
        if (self::shouldBail($patient)) {
            return null;
        }
        
        self::makeStandByNursePrimary($patient);
        self::assignCallToStandByNurse($patient);
    }
    
    private static function assignCallToStandByNurse(User $patient)
    {
        $scheduler = app()->make(SchedulerService::class);

        if ($scheduler->hasScheduledCall($patient)) {
            return null;
        }

        return $scheduler->storeScheduledCall($patient->id, '09:00', '17:00', now(), 'system - patient status changed to enrolled', StandByNurseUser::id());
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
        return self::assign($event->patient);
    }

    private static function makeStandByNursePrimary(User $patient)
    {
        try {
            return PatientNurse::updateOrCreate(
                ['patient_user_id' => $patient->id],
                [
                    'nurse_user_id'           => StandByNurseUser::id(),
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
    
    private static function shouldBail(User $patient)
    {
        $patient->loadMissing('carePlan');
        
        if (! $patient->carePlan) {
            return true;
        }
    
        if (! in_array($patient->carePlan->status, [CarePlan::QA_APPROVED, CarePlan::PROVIDER_APPROVED])) {
            return true;
        }
    
        if ( ! StandByNurseUser::id()) {
            return true;
        }
        
        return false;
    }
}
