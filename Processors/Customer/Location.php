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
use CircleLinkHealth\CcmBilling\Tests\Fakes\FakeMonthlyBillingProcessor;

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
        $locationServiceProcessors = $this->repo->availableLocationServiceProcessors();

        //call job to dispatch jobs per 100-1000 jobs
        $locationPatients = \CircleLinkHealth\Customer\Entities\Location::findOrFail($locationId)->getPatients();

        $fake = new FakeMonthlyBillingProcessor();
        foreach ($locationPatients as $patient) {
            //set stub
            $patientStub = '';
            $fake->process();
        }
    }

    public function repo(): CustomerBillingProcessorRepository
    {
        return $this->repo;
    }
}
