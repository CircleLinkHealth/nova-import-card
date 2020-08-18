<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use App\Call;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AttestedProblem extends Pivot
{
    protected $fillable = [
        'patient_user_id',
        'ccd_problem_id',
        'call_id',
        'ccd_problem_name',
        'ccd_problem_icd_10_code',
        'chargeable_month',
        //todo: deprecate
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
