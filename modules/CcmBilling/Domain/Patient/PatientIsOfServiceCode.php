<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\ForcedPatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientSummaryForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

class PatientIsOfServiceCode
{
    protected bool $billingRevampIsEnabled;

    protected bool $bypassLocationCheck;

    protected PatientMonthlyBillingDTO $dto;
    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    protected string $serviceCode;

    public function __construct(int $patientId, string $serviceCode, bool $bypassLocationCheck = false)
    {
        $this->patientId           = $patientId;
        $this->serviceCode         = $serviceCode;
        $this->bypassLocationCheck = $bypassLocationCheck;
    }

    public static function execute(int $patientId, string $serviceCode, $bypassLocationCheck = false): bool
    {
        return (new static($patientId, $serviceCode, $bypassLocationCheck))
            ->setDto()
            ->isOfServiceCode();
    }

    public static function fromDTO(PatientMonthlyBillingDTO $dto, string $serviceCode, $bypassLocationCheck = false): bool
    {
        return (new static($dto->getPatientId(), $serviceCode, $bypassLocationCheck))
            ->setDto($dto)
            ->isOfServiceCode();
    }

    public function isOfServiceCode(): bool
    {
        if ($this->patientHasForcedService()) {
            return true;
        }

        if ($this->patientHasBlockedService()) {
            return false;
        }

        return $this->hasSummary() && $this->hasEnoughProblems() && $this->patientLocationHasService() && ! $this->hasClashingService();
    }

    private function billingRevampIsEnabled(): bool
    {
        return BillingCache::billingRevampIsEnabled();
    }

    private function hasClashingService(): bool
    {
        if ($this->isAClashForForcedService()) {
            return true;
        }
        $clashes = ClashingChargeableServices::getClashesOfService($this->serviceCode);

        if (empty($clashes)) {
            return false;
        }

        foreach ($clashes as $clashingService) {
            if (PatientIsOfServiceCode::fromDTO($this->dto, $clashingService)) {
                return true;
            }
        }

        return false;
    }

    private function hasEnoughProblems(): bool
    {
        return $this->problemsOfServiceCount() >= $this->minimumProblemCountForService();
    }

    private function hasSummary(): bool
    {
        if ( ! $this->billingRevampIsEnabled()) {
            if (ChargeableService::BHI === $this->serviceCode) {
                return ! $this->requiresPatientBhiConsent();
            }

            return true;
        }

        return collect($this->dto->getPatientServices())
            ->filter(fn (PatientSummaryForProcessing $service) => $service->getCode() === $this->serviceCode)
            ->isNotEmpty();
    }

    private function isAClashForForcedService(): bool
    {
        return collect($this->dto->getForcedPatientServices())
            ->filter(function (ForcedPatientChargeableServicesForProcessing $fcs) {
                $clashes = ClashingChargeableServices::getClashesOfService($fcs->getChargeableServiceCode());

                return in_array($this->serviceCode, $clashes);
            })
            ->isNotEmpty();
    }

    private function minimumProblemCountForService(): int
    {
        return PatientProblemsForBillingProcessing::SERVICE_PROBLEMS_MIN_COUNT_MAP[ChargeableService::getCodeForPatientProblems($this->serviceCode)] ?? 0;
    }

    private function patientHasBlockedService(): bool
    {
        return collect($this->dto->getForcedPatientServices())
            ->filter(fn (ForcedPatientChargeableServicesForProcessing $service) => $service->getChargeableServiceCode() === $this->serviceCode && $service->isBlocked())
            ->isNotEmpty();
    }

    private function patientHasForcedService(): bool
    {
        return collect($this->dto->getForcedPatientServices())
            ->filter(fn (ForcedPatientChargeableServicesForProcessing $service) => $service->getChargeableServiceCode() === $this->serviceCode && $service->isForced())
            ->isNotEmpty();
    }

    private function patientLocationHasService(): bool
    {
        if ($this->bypassLocationCheck) {
            return true;
        }

        if ( ! $this->billingRevampIsEnabled()) {
            return in_array($this->serviceCode, $this->dto->getPracticeCodes());
        }

        return collect($this->dto->getLocationServices())
            ->filter(fn (LocationChargeableServicesForProcessing $service) => $service->getCode() === $this->serviceCode)
            ->isNotEmpty();
    }

    private function problemsOfServiceCount(): int
    {
        return collect($this->dto->getPatientProblems())
            ->filter(fn (PatientProblemForProcessing $p) => in_array($this->serviceCode, $p->getServiceCodes()))
            ->count();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function requiresPatientBhiConsent(): bool
    {
        return ! User::hasBhiConsent()
            ->whereId($this->patientId)
            ->exists();
    }

    //todo: ommit patient time from and other needless data from DTO
    private function setDto(?PatientMonthlyBillingDTO $dto = null): self
    {
        $this->dto = $dto ?? PatientMonthlyBillingDTO::generateFromUser(
            $this->repo()->getPatientWithBillingDataForMonth($this->patientId, $month = Carbon::now()->startOfMonth()),
            $month
        );

        return $this;
    }
}
