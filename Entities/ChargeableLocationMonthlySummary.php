<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;

/**
 * CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $location_id
 * @property int|null                                                                                    $chargeable_service_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property string                                                                                      $amount
 * @property bool                                                                                        $is_locked
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property ChargeableService|null                                                                      $chargeableService
 * @property Location                                                                                    $location
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary query()
 * @mixin \Eloquent
 */
class ChargeableLocationMonthlySummary extends BaseModel
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

    public function getServiceCode()
    {
        return optional($this->chargeableService)->code;
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
