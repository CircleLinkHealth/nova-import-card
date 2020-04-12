<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\SharedModels\HasProblemCodes;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends BaseModel implements \CircleLinkHealth\SharedModels\Contracts\Problem
{
    use HasProblemCodes;
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'is_monitored',
        'problem_import_id',
        'ccda_id',
        'patient_id',
        'ccd_problem_log_id',
        'name',
        'billable',
        'cpm_problem_id',
        'cpm_instruction_id',
    ];

    protected $table = 'ccd_problems';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes()
    {
        return $this->hasMany(ProblemCode::class);
    }

    public function cpmInstruction()
    {
        return $this->hasOne(CpmInstruction::class, 'id', 'cpm_instruction_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class, 'cpm_problem_id', 'id');
    }

    public function getNameAttribute($name)
    {
        $this->original_name = $name;
        if ($this->cpm_problem_id) {
            return optional($this->cpmProblem)->name;
        }

        return $name;
    }

    public function icd10Code()
    {
        $icd10 = $this->icd10Codes->first();

        if ($icd10) {
            return $icd10->code;
        }

        return $this->cpmProblem->default_icd_10_code ?? '';
    }

    public function isBehavioral(): bool
    {
        return (bool) optional($this->cpmProblem)->is_behavioral;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function patientSummaries()
    {
        return $this->belongsToMany(PatientMonthlySummary::class, 'patient_summary_problems', 'problem_id')
            ->withPivot('name', 'icd_10_code')
            ->withTimestamps();
    }
}
