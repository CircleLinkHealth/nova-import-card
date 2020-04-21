<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use Cache;
use CircleLinkHealth\Customer\Entities\ChargeableService;

trait HasChargeableServices
{
    private $byCode;

    public function chargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
            ->withPivot(['amount'])
            ->withTimestamps();
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

    public function hasServiceCode($code)
    {
        return $this->byCode()->has($code);
    }

    public function scopeHasServiceCode($builder, $code)
    {
        return $builder->whereHas('chargeableServices', function ($q) use ($code) {
            $q->whereCode($code);
        });
    }

    private function byCode()
    {
        if ( ! $this->byCode) {
            $this->byCode = $this->chargeableServices->keyBy('code');
        }

        return $this->byCode;
    }
}
