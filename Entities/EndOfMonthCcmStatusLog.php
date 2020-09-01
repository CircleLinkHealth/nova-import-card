<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog.
 *
 * @property User   $patient
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog newModelQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog newQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog query()
 * @mixin \Eloquent
 */
class EndOfMonthCcmStatusLog extends Model
{
    use DateScopesTrait;
    
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
