<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property int|null                                                                                    $chargeable_service_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property int|null                                                                                    $actor_id
 * @property bool                                                                                        $is_fulfilled
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property ChargeableService|null                                                                      $chargeableService
 * @property User                                                                                        $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary query()
 * @mixin \Eloquent
 */
class ChargeablePatientMonthlySummary extends BaseModel
{
    protected $casts = [
        'is_fulfilled' => 'boolean',
    ];

    protected $dates = [
        'chargeable_month',
    ];
    protected $fillable = [
        'patient_user_id',
        'chargeable_service_id',
        'chargeable_month',
        'actor_id',
        'is_fulfilled',
    ];

    //todo: placeholder for now, maybe move in trait
    public function chargeableService()
    {
        return $this->hasOne(ChargeableService::class, 'chargeable_service_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
