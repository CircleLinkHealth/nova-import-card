<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PatientServicesToAttachForLegacyABP
{
    protected Collection $activities;

    protected array $eligibleServices = [];

    protected array $fulfilledServices = [];

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
        return $this->setActivities()
            ->setEligibleServices()
            ->setFulfilledServicesWithTime()
            ->updatePmsBillableCcmTime()
            ->getFulfilledServices();
    }

    public static function getCollection(PatientMonthlySummary $summary, EloquentCollection $practiceServices): Collection
    {
        return collect((new static($summary, $practiceServices))->get());
    }

    private function getFulfilledServices(): array
    {
        return $this->fulfilledServices;
    }

    private function repo(): PatientServiceProcessorRepository
    {
        if ( ! isset($this->repo)) {
            $this->repo = app(PatientServiceProcessorRepository::class);
        }

        return $this->repo;
    }

    private function setActivities(): self
    {
        $this->activities = Activity::wherePatientId($this->patientId)
            ->with(['provider.roles'])
            ->createdInMonth(Carbon::now()->startOfMonth(), 'performed_at')
            ->get()
            ->collect();

        return $this;
    }

    private function setEligibleCcmCode(): void
    {
        $practiceCcmCs = $this->practiceServices->whereIn('code', [
            ChargeableService::CCM,
            ChargeableService::PCM,
            ChargeableService::RPM,
        ])
            ->pluck('id')
            ->toArray();

        $activityCs = $this->activities->pluck('chargeable_service_id')->unique()->toArray();

        $matchingCcmCodes = array_intersect($practiceCcmCs, $activityCs);
        //get viable CCM CS method if check below passes
        if (count($matchingCcmCodes) > 1) {
            //get latest

            $mostRecentCcmCode = $this->activities
                ->sortByDesc('performed_at')
                ->whereIn('chargeable_service_id', $practiceCcmCs)
                ->first()
                ->chargeable_service_id;

            $csService = $this->practiceServices->firstWhere('id', $mostRecentCcmCode);
            if (ChargeableService::CCM === $csService->code) {
                $this->eligibleServices = array_merge($this->eligibleServices, $this->practiceServices->whereIn('code', ChargeableService::CCM_CODES)->all());
            } elseif (ChargeableService::RPM === $csService->code) {
                $this->eligibleServices = array_merge($this->eligibleServices, $this->practiceServices->whereIn('code', ChargeableService::RPM_CODES)->all());
            } else {
                $this->eligibleServices[] = $csService;
            }

            return;
        }

        $this->eligibleServices[] = $this->practiceServices->firstWhere('id', $matchingCcmCodes[0]);
    }

    private function setEligibleServices(): self
    {
        if ($this->practiceServices->contains('code', '=', ChargeableService::SOFTWARE_ONLY)) {
            $this->eligibleServices[] = $this->practiceServices->firstWhere('code', ChargeableService::SOFTWARE_ONLY);
        }

        if ($this->practiceServices->contains('code', '=', ChargeableService::GENERAL_CARE_MANAGEMENT)) {
            $this->eligibleServices[] = $this->practiceServices->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT);

            return $this;
        }

        $this->setEligibleCcmCode();

        if ($this->practiceServices->contains('code', '=', ChargeableService::BHI)) {
            $this->eligibleServices[] = $this->practiceServices->firstWhere('code', ChargeableService::BHI);
        }

        return $this;
    }

    private function setFulfilledServicesWithTime(): self
    {
        $servicesWithTime = [];

        foreach ($this->eligibleServices as $service) {
            if (ChargeableService::SOFTWARE_ONLY === $service->code) {
                $servicesWithTime[$service->code] = [
                    'id'           => $service->id,
                    'time'         => 0,
                    'is_fulfilled' => 0 === $this->timeFromClhCareCoaches(),
                ];
                continue;
            }

            if (in_array($service->code, ChargeableService::CCM_PLUS_CODES)) {
                $time = $servicesWithTime[ChargeableService::CCM]['time'] ?? 0;
            } elseif (in_array($service->code, ChargeableService::RPM_PLUS_CODES)) {
                $time = $servicesWithTime[ChargeableService::RPM]['time'] ?? 0;
            } else {
                $time = $this->timeForService($service);
            }

            $servicesWithTime[$service->code] = [
                'id'           => $service->id,
                'time'         => $time,
                'is_fulfilled' => $time >= (ChargeableService::REQUIRED_TIME_PER_SERVICE[$service->code] ?? 0),
            ];
        }

        $this->fulfilledServices = array_filter($servicesWithTime, fn ($s) => $s['is_fulfilled']);

        return $this;
    }

    private function timeForService(ChargeableService $service): int
    {
        if (ChargeableService::CCM === $service->code) {
            return $this->activities->whereIn(
                'chargeable_service_id',
                ChargeableService::cached()
                    ->whereIn('code', ChargeableService::CCM_CODES)
                    ->pluck('id')
                    ->toArray()
            )->sum('duration') ?? 0;
        }

        if (ChargeableService::RPM === $service->code) {
            return $this->activities->whereIn(
                'chargeable_service_id',
                ChargeableService::cached()
                    ->whereIn('code', ChargeableService::RPM_CODES)
                    ->pluck('id')
                    ->toArray()
            )->sum('duration') ?? 0;
        }

        return $this->activities->where('chargeable_service_id', $service->id)->sum('duration') ?? 0;
    }

    private function timeFromClhCareCoaches(): int
    {
        return $this->activities
            ->filter(fn ($a) => optional($a->provider)->isCareCoach())
            ->sum('duration') ?? 0;
    }

    private function updatePmsBillableCcmTime(): self
    {
        $this->summary->ccm_time_for_billable_ccm_cs = collect($this->fulfilledServices)->filter(function ($value, $key) {
            return in_array($key, [
                ChargeableService::CCM,
                ChargeableService::PCM,
                ChargeableService::RPM,
                ChargeableService::GENERAL_CARE_MANAGEMENT,
            ]);
        })->first()['time'] ?? 0;
        $this->summary->save();

        return $this;
    }
}
