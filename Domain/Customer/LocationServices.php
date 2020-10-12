<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Support\Facades\Cache;

class LocationServices
{
    protected LocationProcessorRepository $repo;

    public function __construct(LocationProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function hasCcmPlusCodes(int $locationId): bool
    {
        return $this->hasServicesForMonth($locationId, ChargeableService::CCM_PLUS_CODES);
    }

    public static function hasCCMPlusServiceCode(int $locationId, $cacheIt = false): bool
    {
        $static = new static(app(LocationProcessorRepository::class));

        if ($cacheIt) {
            return Cache::remember(
                "location:{$locationId}:hasCcmPlus",
                2,
                function () use ($static, $locationId) {
                    return $static->hasCcmPlusCodes($locationId);
                }
            );
        }

        return $static->hasCcmPlusCodes($locationId);
    }

    public static function hasCodesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month = null)
    {
        return (new static(app(LocationProcessorRepository::class)))
            ->hasServicesForMonth($locationId, $chargeableServiceCodes, $month);
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month = null): bool
    {
        return $this->repo->hasServicesForMonth($locationId, $chargeableServiceCodes, $month ?? Carbon::now()->startOfMonth());
    }
}
