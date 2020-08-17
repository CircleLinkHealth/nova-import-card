<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class ChargeablePatientMonthlySummary extends Model
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

    public function chargeableService()
    {
        return $this->hasOne(ChargeableService::class, 'chargeable_service_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
