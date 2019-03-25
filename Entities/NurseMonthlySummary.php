<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Customer\Entities\Nurse;

/**
 * CircleLinkHealth\Customer\Entities\NurseMonthlySummary.
 *
 * @property int                 $id
 * @property int                 $nurse_id
 * @property string              $month_year
 * @property int                 $accrued_after_ccm
 * @property int                 $accrued_towards_ccm
 * @property int|null            $no_of_calls
 * @property int|null            $no_of_successful_calls
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereAccruedAfterCcm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereAccruedTowardsCcm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereMonthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNoOfCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNoOfSuccessfulCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereNurseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseMonthlySummary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NurseMonthlySummary extends \App\BaseModel
{
    protected $fillable = [
        'nurse_id',
        'month_year',
        'accrued_after_ccm',
        'accrued_towards_ccm',
        'no_of_calls',
        'no_of_successful_calls',
    ];

    public function nurse()
    {
        $this->belongsTo(Nurse::class, 'id', 'nurse_id');
    }
}
