<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Model;

class EndOfMonthCcmStatusLog extends Model
{
    protected $dates = [
        'chargeable_month',
    ];
    protected $fillable = [
        'patient_user_id',
        'chargeable_month',
        'closed_ccm_status',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
