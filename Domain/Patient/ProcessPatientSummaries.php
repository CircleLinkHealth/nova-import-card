<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Entities\User;

class ProcessPatientSummaries
{
    protected Carbon $month;

    protected ?PatientMonthlyBillingDTO $patientDTO;

    protected int $patientId;

    protected ?User $patientUser;

    protected PatientMonthlyBillingProcessor $processor;

    protected PatientServiceProcessorRepository $repo;

    public function __construct(PatientMonthlyBillingProcessor $processor, PatientServiceProcessorRepository $repo)
    {
        $this->processor = $processor;
        $this->repo      = $repo;
    }

    public function execute(int $patientId, Carbon $month): void
    {
        $this->setPatientId($patientId)
            ->setMonth($month)
            ->setPatientUser()
            ->setPatientDto()
            ->process();
    }

    public function fromDTO(PatientMonthlyBillingDTO $dto): void
    {
        $this->setPatientDto($dto)
            ->process();
    }

    public static function wipeAndReprocessForMonth(int $patientUserId, Carbon $month): void
    {
        //todo: general validation that should exist elsewhere
        $static = app(self::class);

        BillingCache::clearPatients([$patientUserId]);
        $patient = $static->repo->getPatientWithBillingDataForMonth($patientUserId, $month);

        $locationSummaries = $patient->patientInfo->location->chargeableServiceSummaries;
        $locationMonthIsClosed = $locationSummaries->isNotEmpty() &&
                                 $locationSummaries->every(function (ChargeableLocationMonthlySummary $summary) {
                                     return $summary->is_locked;
                                 });
        $patientBillingStatusIsTouched = ! is_null(
            $patient->monthlyBillingStatus
                ->filter(fn($mbs) => $mbs->chargeable_month->equals($month))
                ->whereNotNull('actor_id')
                ->first()
        );

        if ($locationMonthIsClosed || $patientBillingStatusIsTouched){
            return;
        }

        $dto = (new PatientMonthlyBillingDTO())
            ->subscribe(
                (app(LocationProcessorRepository::class))
                    ->availableLocationServiceProcessors(
                        $patient->getPreferredContactLocation(),
                        $thisMonth = Carbon::now()->startOfMonth()
                    )
            )
            ->forPatient($patient->id)
            ->ofLocation(intval($patient->getPreferredContactLocation()))
            ->forMonth($thisMonth)
            ->withProblems(...PatientProblemsForBillingProcessing::getArray($patient->id));

        (app(self::class))->fromDTO($dto);
    }

    private function process()
    {
        if ( ! isset($this->patientDTO) || is_null($this->patientDTO)) {
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) Patient({$this->patientId}) Billing Data are invalid. (DTO is null)");

            return;
        }
        $this->processor->process($this->patientDTO);
    }

    private function setMonth(Carbon $month): self
    {
        $this->month = $month;

        return $this;
    }

    private function setPatientDto(PatientMonthlyBillingDTO $dto = null): self
    {
        if ( ! is_null($dto)) {
            $this->patientDTO = $dto;

            return $this;
        }

        if (is_null($this->patientUser)) {
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) Patient ({$this->patientId}) does not exist.");

            return $this;
        }

        if (is_null($this->patientUser->patientInfo->location)) {
            sendSlackMessage('#billing_alerts', "Warning! (ProcessPatientSummaries:) ({$this->patientUser->id}) does not have location attached.");

            return $this;
        }

        $this->patientDTO = (new PatientMonthlyBillingDTO())
            //todo: preload available service processors on repo property? or will hey exist in patientWithBilling Data array
            ->subscribe($this->patientUser->patientInfo->location->availableServiceProcessors($this->month))
            ->forPatient($this->patientUser->id)
            ->ofLocation($this->patientUser->patientInfo->location->id)
            ->forMonth($this->month)
            ->withForcedPatientServices(
                ...ForcedPatientChargeableServicesForProcessing::fromCollection($this->patientUser->forcedChargeableServices)
            )
            ->withProblems(...PatientProblemsForBillingProcessing::getArray($this->patientId));

        return $this;
    }

    private function setPatientId(int $patientId): self
    {
        $this->patientId = $patientId;

        return $this;
    }

    private function setPatientUser(): self
    {
        $this->patientUser = $this->repo
            ->getPatientWithBillingDataForMonth($this->patientId, $this->month);

        return $this;
    }
}
