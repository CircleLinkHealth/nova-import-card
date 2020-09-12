<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use App\Call;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * CircleLinkHealth\CcmBilling\Entities\AttestedProblem.
 *
 * @property int                             $id
 * @property int|null                        $patient_user_id
 * @property string|null                     $chargeable_month
 * @property int|null                        $call_id
 * @property int                             $ccd_problem_id
 * @property string|null                     $ccd_problem_name
 * @property string|null                     $ccd_problem_icd_10_code
 * @property int|null                        $patient_monthly_summary_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property Call|null                       $call
 * @property Problem                         $ccdProblem
 * @property User|null                       $patient
 * @method   static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem query()
 * @mixin \Eloquent
 * @property int|null $addendum_id
 * @method   static   \Illuminate\Database\Eloquent\Builder|AttestedProblem createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static   \Illuminate\Database\Eloquent\Builder|AttestedProblem createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static   \Illuminate\Database\Eloquent\Builder|AttestedProblem createdThisMonth($field = 'created_at')
 * @method   static   \Illuminate\Database\Eloquent\Builder|AttestedProblem createdToday($field = 'created_at')
 * @method   static   \Illuminate\Database\Eloquent\Builder|AttestedProblem createdYesterday($field = 'created_at')
 */
class AttestedProblem extends Pivot
{
    use DateScopesTrait;

    protected $fillable = [
        'patient_user_id',
        'ccd_problem_id',
        'call_id',
        'ccd_problem_name',
        'ccd_problem_icd_10_code',
        'chargeable_month',
        'patient_monthly_summary_id',
    ];

    protected $table = 'call_problems';

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }

    public function ccdProblem()
    {
        return $this->belongsTo(Problem::class, 'ccd_problem_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
