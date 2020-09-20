<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessor;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Customer\RenewLocationSummaries;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientsChunk;
use CircleLinkHealth\Customer\Entities\Patient;

class Location implements CustomerProcessor
{
    private LocationProcessorRepository $repo;

    public function __construct(LocationProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function fetchApprovablePatients(int $locationId, Carbon $month, int $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->paginatePatients($locationId, $month, $pageSize));
    }

    public function processServicesForAllPatients(int $locationId, Carbon $chargeableMonth): void
    {
        $this->repo()
            ->patientsQuery($locationId, $chargeableMonth, Patient::ENROLLED)
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

    public function processServicesForLocation(int $locationId, Carbon $month)
    {
        if ($this->repo()->hasServicesForMonth($locationId, $month)) {
            return;
        }

        $pastMonthSummaries = $this->repo()->pastMonthSummaries($locationId, $month);

        if ($pastMonthSummaries->isEmpty()) {
            sendSlackMessage('#cpm_general_alerts', "Processing summaries for Location with ID:$locationId failed - no past summaries exist.
            Please head to Location Chargeable Service management and assign chargeable services this location.");

            return;
        }

        RenewLocationSummaries::fromSummariesCollection($pastMonthSummaries, $month);
    }

    public function repo(): LocationProcessorRepository
    {
        return $this->repo;
    }
}
