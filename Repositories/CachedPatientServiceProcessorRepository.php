<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as RepositoryInterface;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class CachedPatientServiceProcessorRepository implements RepositoryInterface
{
    protected Collection $cache;

    protected array $queriedPatients = [];

    protected PatientServiceProcessorRepository $repo;

    public function __construct()
    {
        $this->cache = collect([]);
        $this->repo  = new PatientServiceProcessorRepository();
    }

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->fulfill($patientId, $chargeableServiceCode, $month);

        $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->firstWhere('id', $summary->id)
            ->is_fulfilled = true;

        return $summary;
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): EloquentCollection
    {
        //Carbon month, use filter for correct results?
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->where('chargeable_month', $month);
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        // TODO: Implement getChargeablePatientSummary() method.
        //load views on billing data as well?
        //views are tricky because, I don't think we can update their data in cache.
        //do not use views on this repository?
        //find way to create fake view records?
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummariesView
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->first();
    }

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month = null): ?User
    {
        //is it realistic that multiple months are going to be queried by this?
        //really have to account for other months as well. Maybe don't load only for specific month from the original repo and load everything?
        //also if we load all, we have to specify summary access for other cases
        return $this->getPatientFromCache($patientId);
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->patientInfo
            ->location
            ->chargeableServiceSummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->where('is_fulfilled', true)
            ->count() > 0;
    }

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->setPatientConsented($patientId, $chargeableServiceCode, $month);

        $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries
            ->firstWhere('id', $summary->id)
            ->requires_patient_consent = false;

        $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummariesView
            ->firstWhere('id', $summary->id)
            ->requires_patient_consent = false;

        return $summary;
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->store($patientId, $chargeableServiceCode, $month, $requiresPatientConsent);

        //make sure these changes are stored - push back into cache in every method just to make sure?
        //always store in cache by ID and forget? or don't even make a collection?
        //unresolved ->is user in cache updated?
        $this->getPatientFromCache($patientId)
            ->chargeableMonthlySummaries->push($summary);

        return $summary;
    }

    private function getPatientFromCache(int $patientId): User
    {
        $this->retrievePatientDataIfYouMust($patientId);

        $patient = $this->cache->firstWhere('id', $patientId);

        if (is_null($patient)) {
            throw new \Exception("Could not find Patient with id: $patientId. Billing Processing aborted.");
        }

        if (is_null($patient->patientInfo)) {
            throw new \Exception("Patient with id: $patientId does not have patient info attached. Billing Processing aborted.");
        }

        //todo: help - not sure if we should enforce location check, however billing is now impossible if patient does not have location
        if (is_null($patient->patientInfo->location)) {
            throw new \Exception("Patient with id: $patientId does not have a preferred location attached. Billing Processing aborted.");
        }

        return $patient;
    }

    private function queryPatientData(int $patientId)
    {
        $this->cache->push($this->repo->getPatientWithBillingDataForMonth($patientId));

        $this->queriedPatients[] = $patientId;
    }

    private function retrievePatientDataIfYouMust(int $patientId): void
    {
        if (in_array($patientId, $this->queriedPatients)) {
            return;
        }

        $this->queryPatientData($patientId);
    }
}
