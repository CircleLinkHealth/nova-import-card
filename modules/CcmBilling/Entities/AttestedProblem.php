<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Traits\DateScopesTrait;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Problem;
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
 * @method static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem newModelQuery()
 * @method static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem newQuery()
 * @method static                          \Illuminate\Database\Eloquent\Builder|AttestedProblem query()
 * @mixin \Eloquent
 * @property int|null                   $addendum_id
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdThisMonth($field = 'created_at')
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdToday($field = 'created_at')
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdYesterday($field = 'created_at')
 * @property int|null                   $attestor_id
 * @property PatientMonthlySummary|null $pms
 * @method static                     \Illuminate\Database\Eloquent\Builder|AttestedProblem createdOnIfNotNull(\Carbon\Carbon $date = null, $field = 'created_at')
 */
class AttestedProblem extends Pivot
{
    use DateScopesTrait;

    protected $fillable = [
        'patient_user_id',
        'attestor_id',
        'call_id',
        'ccd_problem_id',
        'ccd_problem_name',
        'ccd_problem_icd_10_code',
        'chargeable_month',
        'patient_monthly_summary_id',
        'addendum_id',
    ];

    protected $dates = [
        'chargeable_month'
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

    public function attestor()
    {
        return $this->belongsTo(User::class, 'attestor_id');
    }

    /**
     * Todo: deprecate.
     */
    public function pms()
    {
        return $this->belongsTo(PatientMonthlySummary::class, 'patient_monthly_summary_id');
    }
}
