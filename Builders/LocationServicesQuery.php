<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Database\Eloquent\Builder;

trait LocationServicesQuery
{
    public function servicesForLocation(int $locationId): Builder
    {
        return ChargeableLocationMonthlySummary::with(['chargeableService'])
            ->where('location_id', $locationId);
    }

    public function servicesForMonth(int $locationId, Carbon $month ): Builder
    {
        return $this->servicesForLocation($locationId)
            ->createdOn($month, 'chargeable_month');
    }
}
