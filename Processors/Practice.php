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
    
    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement billablePatientsQuery() method.
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        return (new Location())->setLocationsIds();
    }
    
    /**
     * @param mixed $practiceIds
     * @return Practice
     */
    public function setPracticeIds($practiceIds)
    {
        $this->practiceIds = $practiceIds;
        return $this;
    }
}
