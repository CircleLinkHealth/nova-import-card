<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use Cache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Chargeable;

trait HasChargeableServices
{
    private $byCode;

    private $byCodeIncludeUnfulfilled;

    /**
     * Include unfulfilled ChargeableServices - services added at the start of month,
     * based on last month's services if last month pms exists, or based on logic seen in:
     * PatientMonthlySummary@.
     *
     * @return mixed
     */
    public function allChargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
            ->using(Chargeable::class)
            ->withPivot([
                'amount',
                'is_fulfilled',
            ])
            ->withTimestamps();
    }

    public function chargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
            ->using(Chargeable::class)
            ->withPivot([
                'amount',
                'is_fulfilled',
            ])
            ->withTimestamps()
            ->where('is_fulfilled', true);
    }

    public function hasAWVServiceCode()
    {
        $class = get_called_class();

        $chargeableServices = Cache::remember(
            "${class}:{$this->id}:chargeableServices",
            2,
            function () {
                return $this->chargeableServices->keyBy('code');
            }
        );

        return $chargeableServices->has(ChargeableService::AWV_INITIAL) || $chargeableServices->has(ChargeableService::AWV_SUBSEQUENT);
    }

    public function hasCCMPlusServiceCode()
    {
        $class = get_called_class();

        $chargeableServices = Cache::remember(
            "${class}:{$this->id}:chargeableServices",
            2,
            function () {
                return $this->chargeableServices->keyBy('code');
            }
        );

        return $chargeableServices->has(ChargeableService::CCM_PLUS_40) || $chargeableServices->has(ChargeableService::CCM_PLUS_60);
    }

    public function hasServiceCode($code, $includeUnfulfilled = false)
    {
        return $this->byCode($includeUnfulfilled)->has($code);
    }

    public function scopeHasServiceCode($builder, $code)
    {
        return $builder->whereHas('chargeableServices', function ($q) use ($code) {
            $q->whereCode($code);
        });
    }

    private function byCode($includeUnfulfilled = false)
    {
        if ($includeUnfulfilled) {
            if ( ! $this->byCodeIncludeUnfulfilled) {
                $this->byCodeIncludeUnfulfilled = $this->allChargeableServices->keyBy('code');
            }

            return $this->byCodeIncludeUnfulfilled;
        }

        if ( ! $this->byCode) {
            $this->byCode = $this->chargeableServices->keyBy('code');
        }

        return $this->byCode;
    }
}
