<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Caches;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;

class BillingDataCache implements BillingCache
{
    protected array $locationSummaryCache = [];
    protected array $patientCache         = [];

    protected array $queriedLocationSummaries = [];

    protected array $queriedPatients = [];

    public function clearLocations(): void
    {
        $this->locationSummaryCache     = [];
        $this->queriedLocationSummaries = [];
    }

    public function clearPatients(): void
    {
        $this->patientCache    = [];
        $this->queriedPatients = [];
    }

    public function forgetLocationSummaries(int $locationId): void
    {
        $this->locationSummaryCache = collect($this->locationSummaryCache)
            ->filter(fn ($locationSummary) => $locationSummary->location_id != $locationId)
            ->toArray();

        $this->queriedLocationSummaries = collect($this->queriedLocationSummaries)
            ->filter(fn ($id) => $id != $locationId)
            ->toArray();
    }

    public function forgetPatient(int $patientId): void
    {
        $this->patientCache = collect($this->patientCache)
            ->filter(fn ($p) => $p->id != $patientId)
            ->toArray();
        $this->queriedPatients = collect($this->patientCache)
            ->filter(fn ($id) => $id != $patientId)
            ->toArray();
    }

    public function getLocationSummaries(int $locationId): ?Collection
    {
        return new Collection(
            collect($this->locationSummaryCache)
                ->where('location_id', $locationId)
                ->toArray()
        );
    }

    public function getPatient(int $patientId): ?User
    {
        return collect($this->patientCache)->firstWhere('id', $patientId);
    }

    public function locationSummariesExistInCache(int $locationId): bool
    {
        return collect($this->locationSummaryCache)
            ->where('location_id', $locationId)
            ->isNotEmpty();
    }

    public function locationWasQueried(int $locationId): bool
    {
        return in_array($locationId, $this->queriedLocationSummaries);
    }

    public function patientExistsInCache(int $patientId): bool
    {
        return collect($this->patientCache)
            ->where('id', $patientId)
            ->isNotEmpty();
    }

    public function patientWasQueried(int $patientId): bool
    {
        return in_array($patientId, $this->queriedPatients);
    }

    public function setLocationSummariesInCache(Collection $summaries): void
    {
        $this->locationSummaryCache = array_merge($this->locationSummaryCache, $summaries->toArray());
    }

    public function setPatientInCache(User $patientUser): void
    {
        $this->patientCache[] = $patientUser;
    }

    public function setQueriedLocation(int $locationId): void
    {
        $this->queriedLocationSummaries[] = $locationId;
    }

    public function setQueriedPatient(int $patientId): void
    {
        $this->queriedPatients[] = $patientId;
    }
}
