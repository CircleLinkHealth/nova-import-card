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

    protected PatientServiceProcessorRepository $repo;

    public function __construct()
    {
        $this->cache = collect([]);
        $this->repo  = new PatientServiceProcessorRepository();
    }

    //todo: implement before each method, to make sure data are loaded and to avoid cluttering in all methods
//    public function __call($method,$arguments) {
//        if(method_exists($this, $method)) {
//            $this->getPatientWithBillingDataForMonth();
//            return call_user_func_array(array($this,$method),$arguments);
//        }
//    }

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->fulfill($patientId, $chargeableServiceCode, $month);

        $this->cache->firstWhere('id', $patientId)->chargeableMonthlySummaries->firstWhere('id', $summary->id)->is_fulfilled = true;

        return $summary;
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month): EloquentCollection
    {
        //Carbon month, use filter for correct results?
        return $this->cache
            ->firstWhere('id', $patientId)
            ->chargeableMonthlySummaries->where('chargeable_month', $month)
            ->get();
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        // TODO: Implement getChargeablePatientSummary() method.
        //load views on billing data as well?
        //views are tricky because, I don't think we can update their data in cache.
        //do not use views on this repository?
        //find way to create fake view records?
        return $this->cache
            ->firstWhere('id', $patientId)
            ->chargeableMonthlySummariesView
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->first();
    }

    public function getPatientWithBillingDataForMonth(int $patientId, Carbon $month): ?User
    {
        //is it realistic that multiple months are going to be queried by this?
        //really have to account for other months as well. Maybe don't load only for specific month from the original repo and load everything?
        if (is_null($patient = $this->cache->firstWhere('id', $patientId))) {
            $this->cache->push($patient = $this->repo->getPatientWithBillingDataForMonth($patientId, $month));
        }

        return $patient;
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        //todo private method to get patient from cache
        return $this->cache
            ->firstWhere('id', $patientId)
            ->chargeableMonthlySummariesView
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        //todo: load location data on patient
        return $this->cache
            ->firstWhere('id', $patientId)
            ->patientInfo
            ->location
            ->chargeableMonthlySummaries
            ->where('chargeableService.code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->count() > 0;
    }

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->cache
            ->firstWhere('id', $patientId)
            ->chargeableMonthlySummariesView
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('chargeable_month', $month)
            ->where('is_fulfilled', true)
            ->count() > 0;
    }

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->setPatientConsented($patientId, $chargeableServiceCode, $month);

        $this->cache->firstWhere('id', $patientId)->chargeableMonthlySummaries->firstWhere('id', $summary->id)->requires_patient_consent = false;

        return $summary;
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        $summary = $this->repo->store($patientId, $chargeableServiceCode, $month, $requiresPatientConsent);

        $this->cache->firstWhere('id', $patientId)->chargeableMonthlySummaries->push($summary);

        return $summary;
    }
}
