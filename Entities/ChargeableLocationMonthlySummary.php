<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Database\Eloquent\Model;

class ChargeableLocationMonthlySummary extends Model
{
    protected $casts = [
        'is_locked' => 'boolean',
    ];

    protected $dates = [
        'chargeable_month',
    ];
    protected $fillable = [
        'location_id',
        'chargeable_service_id',
        'chargeable_month',
        'amount',
        'is_locked',
    ];

    //todo: placeholder for now, maybe move in trait
    public function chargeableService()
    {
        return $this->hasOne(ChargeableService::class, 'chargeable_service_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
