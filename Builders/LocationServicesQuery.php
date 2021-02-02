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
    public function servicesForLocations(array $locationIds): Builder
    {
        return ChargeableLocationMonthlySummary::with(['chargeableService'])
            ->whereIn('location_id', $locationIds);
    }

    public function servicesForMonth(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): Builder
    {
        return $this->servicesForLocations($locationIds)
            ->when($excludeLocked, fn ($q) => $q->where('is_locked', false))
            ->createdOnIfNotNull($month, 'chargeable_month');
    }
}
