<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientChargeableServicesForProcessing;
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
        $static = app(self::class);

        BillingCache::clearPatients([$patientUserId]);
        $patient = $static->repo->getPatientWithBillingDataForMonth($patientUserId, $month);

        $dto = (new PatientMonthlyBillingDTO())
            ->subscribe(
                (app(LocationProcessorRepository::class))
                    ->availableLocationServiceProcessors(
                        [$patient->getPreferredContactLocation()],
                        $thisMonth = Carbon::now()->startOfMonth()
                    )
            )
            ->forPatient($patient->id)
            ->ofLocation(intval($patient->getPreferredContactLocation()))
            ->setBillingStatusIsTouched(
                ! is_null(
                    optional(
                        $patient
                            ->monthlyBillingStatus
                            ->filter(fn (PatientMonthlyBillingStatus $mbs) => $mbs->chargeable_month->equalTo($month))
                            ->first()
                    )->actor_id
                )
            )
            ->withLocationServices(
                ...LocationChargeableServicesForProcessing::fromCollection($patient->patientInfo->location->chargeableServiceSummaries)
            )
            ->forMonth($thisMonth)
            ->withProblems(...PatientProblemsForBillingProcessing::getArrayFromPatient($patient))
            ->withPatientServices(
                ...PatientChargeableServicesForProcessing::fromCollection($patient)
            )
            ->withForcedPatientServices(
                ...ForcedPatientChargeableServicesForProcessing::fromCollection($patient->forcedChargeableServices)
            );

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
            ->subscribe($this->patientUser->patientInfo->location->availableServiceProcessors($this->month))
            ->forPatient($this->patientUser->id)
            ->ofLocation($this->patientUser->patientInfo->location->id)
            ->forMonth($this->month)
            ->setBillingStatusIsTouched(
                ! is_null(optional($this->patientUser->monthlyBillingStatus
                    ->filter(fn (PatientMonthlyBillingStatus $mbs) => $mbs->chargeable_month->equalTo($this->month))
                    ->first())->actor_id)
            )
            ->withLocationServices(
                ...LocationChargeableServicesForProcessing::fromCollection($this->patientUser->patientInfo->location->chargeableServiceSummaries)
            )
            ->withPatientServices(
                ...PatientChargeableServicesForProcessing::fromCollection($this->patientUser)
            )
            ->withForcedPatientServices(
                ...ForcedPatientChargeableServicesForProcessing::fromCollection($this->patientUser->forcedChargeableServices)
            )
            ->withProblems(...PatientProblemsForBillingProcessing::getArrayFromPatient($this->patientUser));

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
