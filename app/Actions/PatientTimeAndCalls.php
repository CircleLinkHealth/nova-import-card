<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Actions;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Database\Eloquent\Collection;

class PatientTimeAndCalls
{
    protected Collection $activities;
    protected Collection $calls;
    protected array $patientIds;

    protected array $patientTimeAndCalls;

    public function __construct(array $patientIds)
    {
        $this->patientIds = $patientIds;
    }

    public static function get(array $patientIds): array
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
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', 'call')
                    ->orWhere('sub_type', '=', 'Call Back');
            })
            ->where('status', 'reached')
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
                'rpm_total_time'         => $patientActivities->where('chargeable_service_id', ChargeableService::cached()->whereIn('code', ChargeableService::RPM_CODES)->pluck('id')->toArray())->sum('duration'),
                'no_of_calls'            => $this->calls->where('inbound_cpm_id', $patientId)->count(),
                'no_of_successful_calls' => $this->calls->where('inbound_cpm_id', $patientId)->count(),
            ];
        }
        
        return $this;
    }
}
