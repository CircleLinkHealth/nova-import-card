<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Illuminate\Database\Eloquent\Builder;

trait ApprovablePatientServicesQuery
{
    public function approvablePatientServicesQuery(Carbon $monthYear): Builder
    {
        return  ChargeablePatientMonthlySummary::with(['patient', 'chargeableService'])
            ->has('patient')
            ->has('chargeableService')
            ->createdOn($monthYear, 'chargeable_month');
    }
}
