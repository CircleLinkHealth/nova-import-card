<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;

class Location implements CustomerBillingProcessor
{
    private LocationProcessorEloquentRepository $repo;

    public function __construct(LocationProcessorEloquentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function fetchApprovablePatients(int $locationId, Carbon $month, $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->patients($locationId, $month, $pageSize));
    }

    public function processServicesForAllPatients(int $locationId, Carbon $month): void
    {
        // TODO: Implement processServicesForAllPatients() method.
    }

    public function repo(): CustomerBillingProcessorRepository
    {
        return $this->repo;
    }
}
