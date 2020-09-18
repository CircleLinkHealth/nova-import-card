<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use App\Events\CarePlanWasQAApproved;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
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

    private static function assignCallToStandByNurse(User $patient)
    {
        $scheduler = app()->make(SchedulerService::class);

        if ($nextCall = $scheduler->getScheduledCallForPatient($patient)) {
            if ( ! $nextCall->outbound_cpm_id) {
                $nextCall->outbound_cpm_id = StandByNurseUser::id();
                $nextCall->save();
            }

            return null;
        }

        return $scheduler->storeScheduledCall($patient->id, '09:00', '17:00', now()->isAfter(now()->setTime(15, 0)) ? now()->addDay() : now(), 'system - patient status changed to enrolled', StandByNurseUser::id());
    }

    private static function makeStandByNursePrimary(User $patient)
    {
        try {
            return app(NurseFinderEloquentRepository::class)->assign($patient->id, StandByNurseUser::id());
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
        if ( ! $patient->isParticipant()) {
            return true;
        }

        $patient->loadMissing('carePlan');

        if ( ! $patient->carePlan) {
            return true;
        }

        if ( ! in_array($patient->carePlan->status, [CarePlan::QA_APPROVED, CarePlan::RN_APPROVED, CarePlan::PROVIDER_APPROVED])) {
            return true;
        }

        if ( ! StandByNurseUser::id()) {
            return true;
        }

        return false;
    }
}
