<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;

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
 * @method   static      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary createdThisMonth($field = 'created_at')
 * @method   static      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary createdToday($field = 'created_at')
 * @method   static      \Illuminate\Database\Eloquent\Builder|ChargeableLocationMonthlySummary createdYesterday($field = 'created_at')
 * @property string|null $status
 */
class ChargeableLocationMonthlySummary extends BaseModel
{
    use DateScopesTrait;

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

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
    }

    public function getServiceCode(): ?string
    {
        return optional($this->chargeableService)->code;
    }

    public function getServiceProcessor(): ?PatientServiceProcessor
    {
        return $this->chargeableService->processor();
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
