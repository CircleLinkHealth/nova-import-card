<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use Illuminate\Support\Collection;

class Practice implements CustomerProcessor
{
    private PracticeProcessorRepository $repo;

    public function __construct(PracticeProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function closeMonth(int $actorId, int $practiceId, Carbon $month)
    {
        return $this->repo->closeMonth($actorId, $practiceId, $month);
    }

    public function counts(int $practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        /** @var Collection|PatientMonthlyBillingStatus[] $statuses */
        $statuses = $this->repo->billingStatuses($practiceId, $month);
        $approved = $statuses->where('status', '=', 'approved')->count();
        $rejected = $statuses->where('status', '=', 'rejected')->count();
        $needQa   = $statuses->where('status', '=', 'needs_qa')->count();
        $other    = $statuses->whereNull('status')->count();

        return new BillablePatientsCountForMonthDTO($approved, $needQa, $rejected, $other);
    }

    public function fetchApprovablePatients(int $practiceId, Carbon $month, int $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->paginatePatients($practiceId, $month, $pageSize));
    }

    public function openMonth(int $practiceId, Carbon $month)
    {
        return $this->repo->openMonth($practiceId, $month);
    }

    public function processServicesForAllPatients(int $practiceId, Carbon $month): void
    {
        // TODO: Implement processServicesForAllPatients() method.
    }

    public function repo(): PracticeProcessorRepository
    {
        return $this->repo;
    }
}
