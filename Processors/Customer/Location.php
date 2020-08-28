<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientsChunk;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;

class Location implements CustomerBillingProcessor
{
    private LocationProcessorEloquentRepository $repo;

    public function __construct(LocationProcessorEloquentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function fetchApprovablePatients(int $locationId, Carbon $month, int $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->paginatePatients($locationId, $month, $pageSize));
    }

    public function processServicesForAllPatients(int $locationId, Carbon $chargeableMonth, bool $fulfill): void
    {
        $this->repo
            ->patientsQuery($locationId, $chargeableMonth)
            ->chunkIntoJobs(
                100,
                new ProcessLocationPatientsChunk(
                    $this->repo->availableLocationServiceProcessors(
                        $locationId,
                        $chargeableMonth
                    ),
                    $chargeableMonth
                )
            );
    }

    public function repo(): CustomerBillingProcessorRepository
    {
        return $this->repo;
    }
}
