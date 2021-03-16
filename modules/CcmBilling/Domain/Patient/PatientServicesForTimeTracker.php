<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummary;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceForTimeTrackerDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientTimeForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Support\Collection;

class PatientServicesForTimeTracker
{
    private const NON_TIME_TRACKABLE_SERVICES = [
        ChargeableService::AWV_INITIAL,
        ChargeableService::AWV_SUBSEQUENT,
        ChargeableService::CCM_PLUS_40,
        ChargeableService::CCM_PLUS_60,
        ChargeableService::RPM40,
        ChargeableService::RPM60,
    ];

    protected PatientMonthlyBillingDTO $dto;

    protected Carbon $month;

    protected ?Collection $monthlyTimes;

    //todo:deperacte when we switch over to new billing entirely
    protected User $patient;

    protected int $patientId;

    protected PatientServiceProcessorRepository $repo;

    public function __construct(int $patientId, Carbon $month)
    {
        $this->patientId = $patientId;
        $this->month     = $month->startOfMonth();
    }

    public function get(): PatientChargeableSummaryCollection
    {
        return $this->setPatientData()
            ->consolidateSummaryData()
            ->createAndReturnResource();
    }

    public function getRaw(): Collection
    {
        return $this->setPatientData()
            ->consolidateSummaryData()
            ->monthlyTimes;
    }

    private function consolidateSummaryData(): self
    {
        return $this->filterUsingPatientServiceStatus()
            ->rejectNonTimeTrackerServices();
    }

    private function createAndReturnResource(): PatientChargeableSummaryCollection
    {
        return new PatientChargeableSummaryCollection(
            $this->monthlyTimes->transform(
                fn (PatientServiceForTimeTrackerDTO $patientService) => new PatientChargeableSummary($patientService)
            )
        );
    }

    private function createFauxMonthlyTimesFromLegacyData(): Collection
    {
        $summaries = collect();

        if ($this->patientEligibleForRHC()) {
            /** @var ChargeableService $rhc */
            $rhc = ChargeableService::cached()->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT);

            $duration = Activity::wherePatientId($this->patientId)
                ->createdThisMonth('performed_at')
                ->where('chargeable_service_id', $rhc->id)
                ->sum('duration');

            $summaries->push(PatientServiceForTimeTrackerDTO::fromArray([
                'patient_id'                      => $this->patientId,
                'chargeable_service_id'           => $rhc->id,
                'chargeable_service_code'         => $rhc->code,
                'chargeable_service_display_name' => $rhc->display_name,
                'total_time'                      => $duration,
            ]));

            return $summaries;
        }

        $servicesDerivedFromPatientProblems = collect($this->dto->getPatientProblems())
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten();

        $this->patient
            ->forcedChargeableServices
            ->where('action_type', PatientForcedChargeableService::FORCE_ACTION_TYPE)
            ->each(fn ($s) => $servicesDerivedFromPatientProblems->push($s->chargeableService->code));

        $servicesDerivedFromPatientProblems->filter()->unique();

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::CCM)) {
            $servicesDerivedFromPatientProblems->push(...ChargeableService::CCM_PLUS_CODES);
        }

        if ($servicesDerivedFromPatientProblems->contains(ChargeableService::RPM)) {
            $servicesDerivedFromPatientProblems->push(...ChargeableService::RPM_PLUS_CODES);
        }

        $chargeableServices = ChargeableService::cached()
            ->whereIn('code', $servicesDerivedFromPatientProblems)
            ->collect();

        if ($chargeableServices->isEmpty()) {
            $summaries->push(
                PatientServiceForTimeTrackerDTO::fromArray([
                    'patient_id'                                     => $this->patientId,
                    'chargeable_service_id'                          => -1,
                    'chargeable_service_display_name'                => 'NONE',
                    'chargeable_service_code'                        => 'NONE',
                    'total_time'                                     => optional(collect($this->dto->getPatientTimes())
                        ->filter(fn (PatientTimeForProcessing $item) => is_null($item->getChargeableServiceId()))
                        ->first())
                        ->getTime() ?? 0,
                ])
            );

            return $summaries;
        }

        $activitiesForMonth = Activity::wherePatientId($this->patientId)
            ->createdThisMonth('performed_at')
            ->get();

        foreach ($chargeableServices as $service) {
            $summaries->push(
                PatientServiceForTimeTrackerDTO::fromArray([
                    'patient_id'                                     => $this->patientId,
                    'chargeable_service_id'                          => $service->id,
                    'chargeable_service_display_name'                => $service->display_name,
                    'chargeable_service_code'                        => $service->code,
                    'total_time'                                     => optional(collect($this->dto->getPatientTimes())
                        ->filter(fn (PatientTimeForProcessing $item) => $item->getChargeableServiceId() === $service->id)
                        ->first())
                        ->getTime() ?? 0,
                ])
            );
        }

        return $summaries;
    }

    private function filterUsingPatientServiceStatus(): self
    {
        $this->monthlyTimes = $this->monthlyTimes
            ->filter(fn (PatientServiceForTimeTrackerDTO $patientService) => -1 === $patientService->getChargeableServiceId()
                ? true
                : PatientIsOfServiceCode::fromDTO($this->dto, $patientService->getChargeableServiceCode()))
            ->values();

        return $this;
    }

    private function newBillingIsEnabled(): bool
    {
        return BillingCache::billingRevampIsEnabled();
    }

    private function patientEligibleForRHC(): bool
    {
        return $this->patient->primaryPractice->chargeableServices->where(
            'code',
            ChargeableService::GENERAL_CARE_MANAGEMENT
        )->count() > 0;
    }

    private function rejectNonTimeTrackerServices(): self
    {
        $this->monthlyTimes = $this->monthlyTimes
            ->reject(function (PatientServiceForTimeTrackerDTO $patientService) {
                return in_array($patientService->getChargeableServiceCode(), self::NON_TIME_TRACKABLE_SERVICES);
            })
            ->values();

        return $this;
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function setPatientData(): self
    {
        $this->patient      = $this->repo()->getPatientWithBillingDataForMonth($this->patientId, $this->month);
        $this->dto          = PatientMonthlyBillingDTO::generateFromUser($this->patient, $this->month);
        $this->monthlyTimes = $this->newBillingIsEnabled()
            ?
            PatientServiceForTimeTrackerDTO::collectionFromDto($this->dto)
            :
            $this->createFauxMonthlyTimesFromLegacyData();

        return $this;
    }
}
