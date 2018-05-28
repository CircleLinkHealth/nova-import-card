<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/25/18
 * Time: 8:57 PM
 */

namespace App\Traits;


use App\ChargeableService;

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
        return $this->chargeableServices()->whereCode($code)->exists();
    }
}