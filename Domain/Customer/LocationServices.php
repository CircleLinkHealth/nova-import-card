<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LocationServices
{
    protected LocationProcessorRepository $repo;

    public function __construct(LocationProcessorRepository $repo)
    {
        $this->repo = $repo;
    }

    public static function get(?int $locationId, ?Carbon $month = null): Collection
    {
        return (app(self::class))->getChargeableServices($locationId, $month);
    }

    public function getChargeableServices(?int $locationId, ?Carbon $month = null): Collection
    {
        if (is_null($locationId)) {
            return new Collection();
        }

        if ( ! Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)) {
            $location = Location::with('practice.chargeableServices')
                ->first();

            return $location->practice->chargeableServices;
        }

        return $this->repo->getLocationSummaries($locationId, $month)
            ->transform(fn (ChargeableLocationMonthlySummary $summary) => $summary->chargeableService)
            ->filter();
    }

    public static function getUsingServiceId(?int $locationId, int $serviceId, ?Carbon $month = null): ?ChargeableService
    {
        return (app(self::class))->getChargeableServices($locationId, $month)->firstWhere('id', $serviceId);
    }

    public function hasCcmPlusCodes(?int $locationId): bool
    {
        return $this->hasServicesForMonth($locationId, ChargeableService::CCM_PLUS_CODES);
    }

    public static function hasCCMPlusServiceCode(?int $locationId, $cacheIt = false): bool
    {
        $static = app(self::class);

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

    public static function hasServiceCodesForMonth(?int $locationId, array $chargeableServiceCodes, Carbon $month = null): bool
    {
        if (is_null($locationId)) {
            return false;
        }

        return (app(self::class))
            ->hasServicesForMonth($locationId, $chargeableServiceCodes, $month);
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month = null): bool
    {
        return $this->repo->hasServicesForMonth($locationId, $chargeableServiceCodes, $month ?? Carbon::now()->startOfMonth());
    }
}
