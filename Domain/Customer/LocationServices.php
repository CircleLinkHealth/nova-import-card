<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Facades\Cache;

class LocationServices
{
    public static function hasCCMPlusServiceCode(int $locationId, $cacheIt = false): bool
    {
        if ($cacheIt) {
            return Cache::remember(
                "location:{$locationId}:hasCcmPlus",
                2,
                function () use ($locationId) {
                    return self::queryCcmPlusCodeExists($locationId);
                }
            );
        }

        return self::queryCcmPlusCodeExists($locationId);
    }

    public static function queryCcmPlusCodeExists(int $locationId): bool
    {
        return ChargeableLocationMonthlySummary::where('location_id', $locationId)
            ->whereHas('chargeableService', fn ($cs) => $cs->whereIn('code', ChargeableService::CCM_PLUS_CODES))
            ->exists();
    }
}
