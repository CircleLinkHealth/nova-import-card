<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use Modules\CcmBilling\Repositories\PracticeProcessorEloquentRepository;

class Practice implements CustomerBillingProcessor
{
    private PracticeProcessorEloquentRepository $repo;

    public function __construct(PracticeProcessorEloquentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function fetchApprovablePatients(int $practiceId, Carbon $month, $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->patients($practiceId, $month, $pageSize));
    }

    public function processServicesForAllPatients(int $practiceId, Carbon $month): void
    {
        // TODO: Implement processServicesForAllPatients() method.
    }

    public function repo(): CustomerBillingProcessorRepository
    {
        return $this->repo;
    }
}
