<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Traits\DateScopesTrait;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog.
 *
 * @property User   $patient
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog newModelQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog newQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog query()
 * @mixin \Eloquent
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property string                                                                                      $closed_ccm_status
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdThisMonth($field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdToday($field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdYesterday($field = 'created_at')
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|EndOfMonthCcmStatusLog createdOnIfNotNull(\Carbon\Carbon $date = null, $field = 'created_at')
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 */
class EndOfMonthCcmStatusLog extends BaseModel
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

    public static function logsExistForMonth(Carbon $month)
    {
        return self::where('chargeable_month', $month->copy()->startOfMonth())
            ->exists();
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
