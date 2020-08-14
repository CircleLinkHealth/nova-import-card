<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcesor;
use Illuminate\Database\Eloquent\Builder;

class Practice implements CustomerBillingProcesor
{
    private $practiceIds;
    
    /**
     * Location constructor.
     */
    public function __construct(array $practiceIds)
    {
        $this->practiceIds = $practiceIds;
    }
    
    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement billablePatientsQuery() method.
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement patientBillableServicesQuery() method.
    }
}
