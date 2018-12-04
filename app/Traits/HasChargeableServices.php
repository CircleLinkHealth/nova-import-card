<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/25/18
 * Time: 8:57 PM
 */

namespace App\Traits;

use App\ChargeableService;
use Cache;

trait HasChargeableServices
{
    public function chargeableServices()
    {
        return $this->morphToMany(ChargeableService::class, 'chargeable')
                    ->withPivot(['amount'])
                    ->withTimestamps();
    }

    public function hasServiceCode($code)
    {
        $class = get_called_class();

        $chargeableServices = Cache::remember(
            "$class:{$this->id}:chargeableServices",
            2,
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
