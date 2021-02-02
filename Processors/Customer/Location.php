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
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Location implements CustomerProcessor
{
    private LocationProcessorRepository $repo;

    public function __construct(LocationProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function closeMonth(array $locationIds, Carbon $month, int $actorId): void
    {
        $this->repo->closeMonth($locationIds, $month, $actorId);
    }

    public function counts(array $locationIds, Carbon $month): BillablePatientsCountForMonthDTO
    {
        /** @var Collection|PatientMonthlyBillingStatus[] $statuses */
        $statuses = $this->repo->approvableBillingStatuses($locationIds, $month)->get();
        $approved = $statuses->where('status', '=', 'approved')->count();
        $rejected = $statuses->where('status', '=', 'rejected')->count();
        $needQa   = $statuses->where('status', '=', 'needs_qa')->count();
        $other    = $statuses->whereNull('status')->count();

        return new BillablePatientsCountForMonthDTO($approved, $needQa, $rejected, $other);
    }

    public function fetchApprovablePatients(array $locationIds, Carbon $month, int $pageSize = 30): LengthAwarePaginator
    {
        $collection = $this->repo
            ->approvableBillingStatuses($locationIds, $month, true)
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

    public function isLockedForMonth(array $locationIds, string $chargeableServiceCode, Carbon $month): bool
    {
        return $this->repo->isLockedForMonth($locationIds, $chargeableServiceCode, $month);
    }

    public function openMonth(array $locationIds, Carbon $month): void
    {
        $this->repo->openMonth($locationIds, $month);
    }

    public function processServicesForAllPatients(array $locationIds, Carbon $chargeableMonth): void
    {
        $this->repo()
            ->locationPatients($locationIds, Patient::ENROLLED)
            ->chunkIntoJobs(
                100,
                new ProcessLocationPatientsChunk(
                    $locationIds,
                    $this->repo->availableLocationServiceProcessors(
                        $locationIds,
                        $chargeableMonth
                    ),
                    $chargeableMonth
                )
            );
    }

    public function processServicesForLocations(array $locationIds, Carbon $month)
    {
        if ($this->repo()->servicesExistForMonth($locationIds, $month)) {
            return;
        }

        $pastMonthSummaries = $this->repo()->pastMonthSummaries($locationIds, $month);

        if ($pastMonthSummaries->isEmpty()) {
            $str = implode(', ', $locationIds);
            sendSlackMessage('#cpm_general_alerts', "Processing summaries for Location with ID:$str failed - no past summaries exist.
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
