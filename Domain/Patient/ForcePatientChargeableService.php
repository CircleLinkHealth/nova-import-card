<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Customer\Entities\User;

class ForcePatientChargeableService
{
    protected ForceAttachInputDTO $input;

    public function __construct(ForceAttachInputDTO $input)
    {
        $this->input = $input;
    }

    //todo: change to actually performing the action through repo - the idea is to be able to validate input before performing any actions
    //like for example know what/when to delete. 1. Don't delete data for billed month 2. Handle Perma deletions if exist
    public static function execute(ForceAttachInputDTO $input): void
    {
        if (! self::shouldProceedToDatabase($input)){
            return;
        }

        $repo = app(PatientServiceProcessorRepository::class);
        $input->isDetaching()
            ? $repo->detachForcedChargeableService($input->getPatientUserId(), $input->getChargeableServiceId(), $input->getMonth(), $input->getActionType())
            : $repo->attachForcedChargeableService($input->getPatientUserId(), $input->getChargeableServiceId(), $input->getMonth(), $input->getActionType());
    }

    public static function handleObserverEvents(ForceAttachInputDTO $input): void
    {
        (new static($input))
            ->guaranteeHistoricallyAccurateRecords()
            ->reprocessPatientForBilling();
    }

    public static function shouldProceedToDatabase(ForceAttachInputDTO $input):bool
    {
        if ($input->isPermanent()){
            return true;
        }
        if ($input->isDetaching()){
            return ! PatientMonthlyBillingStatus::where('patient_user_id', $input->getPatientUserId())
                                      ->where('chargeable_month', $input->getMonth())
                                      ->where(function ($q) {
                                          $q->whereNotNull('actor_id')
                                            ->orWhere('status', 'approved');
                                      })
                                      ->exists();
        }

        //todo: should we check for existing blocks or is this handled by observer events static method?
        return true;
    }

    private function createHistoricalRecords(User $patient, Carbon $startingMonth, Carbon $endingMonth, string $actionType): void
    {
        $start    = $startingMonth->copy();
        $end      = $endingMonth->copy();
        $toCreate = [];
        //todo: needs more logic
        while ($start->lt($end)) {
            $toCreate[] = [
                'chargeable_service_id' => $this->input->getChargeableServiceId(),
                'chargeable_month'      => $start->startOfMonth()->copy(),
                'action_type'           => $this->input->getActionType(),
            ];

            $start->addMonth();
        }
        //create or update?
        $patient->forcedChargeableServices()->createMany($toCreate);
    }

    private function guaranteeHistoricallyAccurateRecords(): self
    {
        //todo: use repo - not cached repo though, because case of deleting a perm for another perm
        $patient = User::ofType('participant')
            ->with([
                'forcedChargeableServices.chargeableService',
                'monthlyBillingStatus'
            ])
            ->findOrFail($this->input->getPatientUserId());

        if ($this->input->isDetaching() && $this->input->isPermanent()) {
            $this->createHistoricalRecords($patient, $this->input->getEntryCreatedAt(), Carbon::now()->startOfMonth(), $this->input->getActionType());

            return $this;
        }

        $opposingActionType      = PatientForcedChargeableService::getOpposingActionType($this->input->getActionType());
        $opposingPermanentAction = $patient->forcedChargeableServices
            ->whereNull('chargeable_month')
            ->where('chargeable_service_id', $this->input->getChargeableServiceId())
            ->where('action_type', $opposingActionType)
            ->first();

        if ($this->input->isPermanent() && ! is_null($opposingPermanentAction)) {
            (app(PatientServiceProcessorRepository::class))->detachForcedChargeableService(
                $this->input->getPatientUserId(),
                $this->input->getChargeableServiceId(),
                null,
                $opposingActionType);
        }

        //if type is force,
        //1.remove block from month if exists
        //2.if perma block, convert to historical records if you must
        //3.if month, check for perma, and log error somewhere if perma exists.

        //if type is block above or opposite
        //if perma check opposite perma, detach, and create historical records

        return $this;
    }

    private function reprocessPatientForBilling(): void
    {
        //todo: if permanent(null month) reprocess past month as well if it's open
        //add user model to class
        ProcessPatientSummaries::wipeAndReprocessForMonth(
            $this->input->getPatientUserId(),
            is_null($this->input->getMonth()) ? Carbon::now()->startOfMonth() : $this->input->getMonth()
        );
    }
}
