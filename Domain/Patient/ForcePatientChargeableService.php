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
    protected PatientServiceProcessorRepository $repo;

    public function __construct(ForceAttachInputDTO $input)
    {
        $this->input = $input;
        $this->repo = app(PatientServiceProcessorRepository::class);
    }

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

    private function createHistoricalRecords(Carbon $startingMonth, Carbon $endingMonth, string $actionType): void
    {
        $start    = $startingMonth->copy();

        while ($start->lt($endingMonth)) {
            $this->repo->attachForcedChargeableService(
                $this->input->getPatientUserId(),
                $this->input->getChargeableServiceId(),
                $start->startOfMonth()->copy(),
                $this->input->getActionType(),
                $this->input->getReason());

            $start->addMonth();
        }
    }

    private function guaranteeHistoricallyAccurateRecords(): self
    {
        if (! $this->input->isPermanent()){
            return $this;
        }

        $patient = User::ofType('participant')
            ->with([
                'forcedChargeableServices.chargeableService',
                'monthlyBillingStatus'
            ])
            ->findOrFail($this->input->getPatientUserId());

        $opposingActionType      = PatientForcedChargeableService::getOpposingActionType($this->input->getActionType());
        $opposingPermanentAction = $patient->forcedChargeableServices
            ->whereNull('chargeable_month')
            ->where('chargeable_service_id', $this->input->getChargeableServiceId())
            ->where('action_type', $opposingActionType)
            ->first();

        if ($this->input->isDetaching() && $this->input->isPermanent()) {
            $endingMonth = Carbon::now()->startOfMonth();
            if (! is_null($opposingPermanentAction) && $this->input->getEntryCreatedAt()->lessThan($opposingPermanentAction->created_at)){
                $endingMonth = $opposingPermanentAction->created_at->startOfMonth();
            }

            $this->createHistoricalRecords($this->input->getEntryCreatedAt(), $endingMonth, $this->input->getActionType());

            return $this;
        }

        if ($this->input->isPermanent() && ! is_null($opposingPermanentAction)) {
            $this->repo->detachForcedChargeableService(
                $this->input->getPatientUserId(),
                $this->input->getChargeableServiceId(),
                null,
                $opposingActionType);
        }

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
