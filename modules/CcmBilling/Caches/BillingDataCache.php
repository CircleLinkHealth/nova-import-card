<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Caches;

use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\User;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Database\Eloquent\Collection;

class BillingDataCache implements BillingCache
{
    protected bool $billingRevampIsEnabled;
    protected array $locationSummaryCache = [];
    protected array $patientCache         = [];

    protected array $queriedLocationSummaries = [];

    protected array $queriedPatients = [];

    public function billingRevampIsEnabled(): bool
    {
        if ( ! isset($this->billingRevampIsEnabled)) {
            $this->billingRevampIsEnabled = Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG);
        }

        return $this->billingRevampIsEnabled;
    }

    public function clearLocations(): void
    {
        $this->locationSummaryCache     = [];
        $this->queriedLocationSummaries = [];
    }

    public function clearPatients(array $patientIds = []): void
    {
        if (empty($patientIds)) {
            $this->patientCache    = [];
            $this->queriedPatients = [];
        } else {
            $this->patientCache    = array_filter($this->patientCache, fn ($p) => ! in_array($p->id, $patientIds));
            $this->queriedPatients = array_filter($this->queriedPatients, fn ($pId) => ! in_array($pId, $patientIds));
        }
    }

    public function forgetLocationSummaries(array $locationIds): void
    {
        $this->locationSummaryCache = collect($this->locationSummaryCache)
            ->filter(fn ($locationSummary) => ! in_array($locationSummary->location_id, $locationIds))
            ->all();

        $this->queriedLocationSummaries = collect($this->queriedLocationSummaries)
            ->filter(fn ($id) => ! in_array($id, $locationIds))
            ->all();
    }

    public function forgetPatient(int $patientId): void
    {
        $this->patientCache = collect($this->patientCache)
            ->filter(fn ($p) => $p->id != $patientId)
            ->all();
        $this->queriedPatients = collect($this->patientCache)
            ->filter(fn ($id) => $id != $patientId)
            ->all();
    }

    public function getLocationSummaries(array $locationIds): ?Collection
    {
        return new Collection(
            collect($this->locationSummaryCache)
                ->whereIn('location_id', $locationIds)
                ->all()
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

    public function setBillingRevampIsEnabled(bool $isEnabled): void
    {
        $this->billingRevampIsEnabled = $isEnabled;
    }

    public function setLocationSummariesInCache(Collection $summaries): void
    {
        $this->locationSummaryCache = array_merge($this->locationSummaryCache, $summaries->all());
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
