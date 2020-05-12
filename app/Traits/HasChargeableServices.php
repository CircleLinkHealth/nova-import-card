<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use Cache;
use CircleLinkHealth\Customer\Entities\ChargeableService;

trait HasChargeableServices
{
    //todo: do we need to deprecate this in favor of the Customer Version?
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
            120,
            function () {
                return $this->chargeableServices->keyBy('code');
            }
        );

        return $chargeableServices->has('AWV: G0438') || $chargeableServices->has('AWV: G0439');
    }

    public function hasServiceCode($code)
    {
        $class = get_called_class();

        $chargeableServices = Cache::remember(
            "${class}:{$this->id}:chargeableServices",
            120,
            function () {
                return $this->chargeableServices->keyBy('code');
            }
        );

        return $chargeableServices->has($code);
    }

    public function scopeHasServiceCode($builder, $code)
    {
        return $builder->whereHas('chargeableServices', function ($q) use ($code) {
            $q->whereCode($code);
        });
    }
}
