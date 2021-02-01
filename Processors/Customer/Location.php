<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessor;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Customer\RenewLocationSummaries;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatient;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientsChunk;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

class Location implements CustomerProcessor
{
    private LocationProcessorRepository $repo;

    public function __construct(LocationProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function fetchApprovablePatients(int $locationId, Carbon $month, int $pageSize = 30): LengthAwarePaginator
    {
        $collection = $this->repo->patientsQuery($locationId, $month)
            ->paginate($pageSize);

        $rawArray = collect($collection->items())
            ->map(fn (PatientMonthlyBillingStatus $billingStatus) => ApprovablePatient::make($billingStatus)->toArray(null));

        return new LengthAwarePaginator(
            $rawArray,
            $collection->total(),
            $collection->perPage(),
            $collection->currentPage(),
            ['path' => request()->url()]
        );
    }

    public function isLockedForMonth(int $locationId, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->repo->isLockedForMonth($locationId, $chargeableServiceCode, $month);
    }

    public function processServicesForAllPatients(int $locationId, Carbon $chargeableMonth): void
    {
        $this->repo()
            ->locationPatients($locationId, Patient::ENROLLED)
            ->chunkIntoJobs(
                100,
                new ProcessLocationPatientsChunk(
                    $locationId,
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
        if ($this->repo()->servicesExistForMonth($locationId, $month)) {
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
