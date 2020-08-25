<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Database\Eloquent\Builder;

trait AvailableLocationServicesQuery
{
    public function servicesForMonth($locationId, Carbon $chargeableMonth): Builder
    {
        return ChargeableLocationMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select('code');
        }])
            ->where('location_id', $locationId)
            ->createdOn($chargeableMonth, 'chargeable_month');
    }
}
