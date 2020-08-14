<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\BillingProcesor;
use Illuminate\Database\Eloquent\Builder;

class Practice implements BillingProcesor
{
    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement billablePatientsQuery() method.
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement patientBillableServicesQuery() method.
    }
}
