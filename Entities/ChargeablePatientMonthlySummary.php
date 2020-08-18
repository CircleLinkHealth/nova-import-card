<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

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
