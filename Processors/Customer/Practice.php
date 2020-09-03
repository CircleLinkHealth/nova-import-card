<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;

class Practice implements CustomerProcessor
{
    private PracticeProcessorRepository $repo;
    
    public function __construct(PracticeProcessorRepository $repo)
    {
        $this->repo = $repo;
    }
    
    public function fetchApprovablePatients(int $practiceId, Carbon $month, int $pageSize = 30): ApprovablePatientCollection
    {
        return new ApprovablePatientCollection($this->repo->paginatePatients($practiceId, $month, $pageSize));
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
