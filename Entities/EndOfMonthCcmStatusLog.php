<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use Carbon\Carbon;
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
 * @property int                             $id
 * @property int                             $patient_user_id
 * @property \Illuminate\Support\Carbon      $chargeable_month
 * @property string                          $closed_ccm_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdThisMonth($field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdToday($field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdYesterday($field = 'created_at')
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
    
    public static function logsExistForMonth(Carbon $month){
        return self::where('chargeable_month', $month->copy()->startOfMonth())
            ->exists();
    }
}
