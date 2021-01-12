<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Actions;

use Carbon\Carbon;
use CircleLinkHealth\Customer\DTO\PatientTimeAndCalls as DTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\Call;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class PatientTimeAndCalls
{
    protected EloquentCollection $activities;
    protected EloquentCollection $calls;
    protected array $patientIds;

    protected array $patientTimeAndCalls = [];

    public function __construct(array $patientIds)
    {
        $this->patientIds = $patientIds;
    }

    public static function get(array $patientIds): Collection
    {
        return (new static($patientIds))
            ->setActivities()
            ->setCalls()
            ->setTimeAndCalls()
            ->formatAndReturnData();
    }

    public static function getRaw(array $patientIds): array
    {
        return (new static($patientIds))
            ->setActivities()
            ->setCalls()
            ->setTimeAndCalls()
            ->returnTimeAndCalls();
    }

    private function callsForPatient(int $patientId): Collection
    {
        return $this->calls->where('inbound_cpm_id', $patientId)
            ->filter(function ($c) {
                return is_null($c->type) || 'call' === $c->type || 'Call Back' === $c->sub_type;
            });
    }

    private function formatAndReturnData(): Collection
    {
        return collect($this->returnTimeAndCalls())->map(function ($item, $key) {
            return (new DTO())
                ->setPatientId($key)
                ->fromArray($item);
        });
    }

    private function returnTimeAndCalls(): array
    {
        return $this->patientTimeAndCalls;
    }

    private function setActivities(): self
    {
        $this->activities = Activity::whereIn('patient_id', $this->patientIds)
            ->createdInMonth(Carbon::now()->startOfMonth(), 'performed_at')
            ->get();

        return $this;
    }

    private function setCalls(): self
    {
        $this->calls = Call::whereIn('inbound_cpm_id', $this->patientIds)
            ->createdInMonth(Carbon::now()->startOfMonth(), 'called_date')
            ->get();

        return $this;
    }

    private function setTimeAndCalls(): self
    {
        foreach ($this->patientIds as $patientId) {
            $patientActivities = $this->activities->where('patient_id', $patientId);

            $this->patientTimeAndCalls[$patientId] = [
                'ccm_total_time'         => $patientActivities->whereIn('chargeable_service_id', ChargeableService::cached()->whereIn('code', ChargeableService::CCM_CODES)->pluck('id')->toArray())->sum('duration'),
                'bhi_total_time'         => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->firstWhere('code', ChargeableService::BHI)->id)->sum('duration'),
                'pcm_total_time'         => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->firstWhere('code', ChargeableService::PCM)->id)->sum('duration'),
                'rpm_total_time'         => $patientActivities->whereIn('chargeable_service_id', ChargeableService::cached()->whereIn('code', ChargeableService::RPM_CODES)->pluck('id')->toArray())->sum('duration'),
                'rhc_total_time'         => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->firstWhere('code', ChargeableService::GENERAL_CARE_MANAGEMENT)->id)->sum('duration'),
                'no_of_calls'            => ($calls = $this->callsForPatient($patientId))->count(),
                'no_of_successful_calls' => $calls->where('status', 'reached')->count(),
            ];
        }

        return $this;
    }
}
