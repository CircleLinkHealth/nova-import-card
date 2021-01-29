<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Customer\Entities\User;

class ForcePatientChargeableService
{
    protected ForceAttachInputDTO $input;

    public function __construct(ForceAttachInputDTO $input)
    {
        $this->input = $input;
    }

    public static function execute(ForceAttachInputDTO $input): void
    {
        (new static($input))
            ->setPatientForcedChargeableService()
            ->guaranteeHistoricallyAccurateRecords()
            ->reprocessPatientForBilling();
    }

    public static function executeWithoutAttaching(ForceAttachInputDTO $input): void
    {
        (new static($input))
            ->guaranteeHistoricallyAccurateRecords()
            ->reprocessPatientForBilling();
    }

    private function createHistoricalRecords(User $patient, Carbon $startingMonth, Carbon $endingMonth, string $actionType): void
    {
        $start    = $startingMonth->copy();
        $end      = $endingMonth->copy();
        $toCreate = [];
        while ($start->lt($end)) {
            $toCreate[] = [
                'chargeable_service_id' => $this->input->getChargeableServiceId(),
                'chargeable_month'      => $start->startOfMonth()->copy(),
                'action_type'           => $this->input->getActionType(),
            ];

            $start->addMonth();
        }
        $patient->forcedChargeableServices()->createMany($toCreate);
    }

    private function guaranteeHistoricallyAccurateRecords(): self
    {
        $patient = User::ofType('participant')
            ->with('forcedChargeableServices.chargeableService')
            ->findOrFail($this->input->getPatientUserId());

        $isPermanent = is_null($this->input->getMonth());
        if ($this->input->isDetaching() && $isPermanent) {
            $this->createHistoricalRecords($patient, $this->input->getEntryCreatedAt(), Carbon::now()->startOfMonth(), $this->input->getActionType());

            return $this;
        }

        $opposingActionType      = PatientForcedChargeableService::getOpposingActionType($this->input->getActionType());
        $opposingPermanentAction = $patient->forcedChargeableServices
            ->whereNull('chargeable_month')
            ->where('chargeable_service_id', $this->input->getChargeableServiceId())
            ->where('action_type', $opposingActionType)
            ->first();

        if ($isPermanent && ! is_null($opposingPermanentAction)) {
            $this->createHistoricalRecords($patient, $opposingPermanentAction->forcedDetails->created_at, Carbon::now()->startOfMonth(), $opposingActionType);
            //detach
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
        ProcessPatientSummaries::wipeAndReprocessForMonth(
            $this->input->getPatientUserId(),
            is_null($this->input->getMonth()) ? Carbon::now()->startOfMonth() : $this->input->getMonth()
        );
    }

    private function setPatientForcedChargeableService(): self
    {
        //set entry created at on dto
        //don't sync, attach
//        User::ofType('participant')
//            ->findOrFail($this->patientUserId)
//            ->forcedChargeableServices()
//            ->syncWithPivotValues([
//                $this->chargeableServiceId,
//            ], [
//                'action_type'      => $this->actionType,
//                'chargeable_month' => $this->month,
//            ], false);

        return $this;
    }
}
