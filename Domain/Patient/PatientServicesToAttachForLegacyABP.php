<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PatientServicesToAttachForLegacyABP
{
    protected Collection $activities;

    protected array $eligibleServices = [];

    protected User $patient;

    protected int $patientId;

    protected EloquentCollection $practiceServices;

    protected PatientServiceProcessorRepository $repo;

    protected PatientMonthlySummary $summary;

    public function __construct(PatientMonthlySummary $summary, EloquentCollection $practiceServices)
    {
        $this->summary          = $summary;
        $this->patientId        = $summary->patient_id;
        $this->practiceServices = $practiceServices;
    }

    public function get(): array
    {
        return $this->setPatient()
            ->setEligibleServices()
            ->setActivities()
            ->returnFulfilledServicesWithTime();
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function returnFulfilledServicesWithTime(): array
    {
        $servicesWithTime = [];

        foreach ($this->eligibleServices as $service) {
            $servicesWithTime[$service->code] = [
                'id'   => $service->id,
                'time' => $time = $this->timeForService($service->id),
            ];
        }

        foreach ($servicesWithTime as $service) {
            if (in_array($service->code, ChargeableService::CCM_PLUS_CODES)) {
                //make sure of order
                $servicesWithTime[ChargeableService::CCM]['time'] += $service['time'];

                continue;
            }

            if (ChargeableService::RPM40 === $service->code) {
                $servicesWithTime[ChargeableService::RPM]['time'] += $service['time'];
                continue;
            }
        }

        foreach ($servicesWithTime as $key => $service) {
            if (in_array($service->code, ChargeableService::CCM_PLUS_CODES)) {
                //make sure of order
                $service['is_fulfilled'] = $servicesWithTime[ChargeableService::CCM]['time'] >= ChargeableService::REQUIRED_TIME_PER_SERVICE[$key] ?? 0;

                continue;
            }

            if (ChargeableService::RPM40 === $service->code) {
                //make sure of order
                $service['is_fulfilled'] = $servicesWithTime[ChargeableService::RPM]['time'] >= ChargeableService::REQUIRED_TIME_PER_SERVICE[$key] ?? 0;
                continue;
            }

            $service['is_fulfilled'] = $service['time'] >= ChargeableService::REQUIRED_TIME_PER_SERVICE[$key] ?? 0;
        }

        return array_filter($servicesWithTime, fn ($s) => $s['is_fulfilled']);
    }

    private function setActivities(): self
    {
        $this->activities = Activity::wherePatientId($this->patientId)
            ->createdInMonth(Carbon::now()->startOfMonth(), 'performed_at')
            ->whereIn('chargeable_service_id', $this->eligibleServices)
            ->get()
            ->collect();

        return $this;
    }

    private function setEligibleServices(): self
    {
        if ($this->practiceServices->contains('code', '=', ChargeableService::GENERAL_CARE_MANAGEMENT)) {
            $this->eligibleServices[] = ChargeableService::GENERAL_CARE_MANAGEMENT;

            return $this;
        }

        $servicesDerivedFromPatientProblems = PatientProblemsForBillingProcessing::getCollection($this->patientId)
            ->transform(fn (PatientProblemForProcessing $p) => $p->getServiceCodes())
            ->flatten()
            ->filter()
            ->unique();

        foreach ($servicesDerivedFromPatientProblems as $service) {
            if (PatientIsOfServiceCode::execute($this->patientId, $service)) {
                $this->eligibleServices[] = $service;
            }

            if (ChargeableService::RPM === $service) {
                $this->eligibleServices[] = ChargeableService::RPM40;
            }

            if (ChargeableService::CCM === $service) {
                $this->eligibleServices = array_merge($this->eligibleServices, ChargeableService::CCM_PLUS_CODES);
            }
        }

        return $this;
    }

    private function setPatient(): self
    {
        if ( ! isset($this->patient)) {
            $this->patient = $this->repo()->getPatientWithBillingDataForMonth($this->patientId);
        }

        return $this;
    }

    private function timeForService(int $chargeableServiceId): int
    {
        return $this->activities->where('chargeable_service_id', $chargeableServiceId)->sum('duration') ?? 0;
    }
}
