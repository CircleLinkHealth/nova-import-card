<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait BillableMonthlyChargeableServicesQuery
{
    public function billableMonthlyChargeableServicesQuery(Carbon $monthYear): Builder
    {
    }
}
