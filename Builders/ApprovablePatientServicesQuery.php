<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait ApprovablePatientServicesQuery
{
    public function approvablePatientServicesQuery(Carbon $monthYear): Builder
    {
    }
}
