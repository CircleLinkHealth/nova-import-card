<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface BillingProcesor
{
    //Returns billable patient monthly summaries equvalent
    public function patientBillableServicesQuery(Carbon $monthYear): Builder;
    
    public function billablePatientsQuery(Carbon $monthYear): Builder;
}
