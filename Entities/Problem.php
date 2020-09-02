<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\HasProblemCodes;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\SharedModels\Entities\Problem.
 *
 * @property int                                                                                                  $id
 * @property int                                                                                                  $is_monitored                                                                                                       A monitored problem is a problem we provide Care Management for.
 * @property int|null                                                                                             $problem_import_id
 * @property int|null                                                                                             $ccda_id
 * @property int                                                                                                  $patient_id
 * @property int|null                                                                                             $ccd_problem_log_id
 * @property string|null                                                                                          $name
 * @property int|null                                                                                             $billable
 * @property int|null                                                                                             $cpm_problem_id
 * @property int|null                                                                                             $cpm_instruction_id                                                                                                 A pointer to an instruction for the ccd problem
 * @property \Illuminate\Support\Carbon|null                                                                      $deleted_at
 * @property \Illuminate\Support\Carbon                                                                           $created_at
 * @property \Illuminate\Support\Carbon                                                                           $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\ProblemCode[]|\Illuminate\Database\Eloquent\Collection       $codes
 * @property int|null                                                                                             $codes_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction                                               $cpmInstruction
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem|null                                              $cpmProblem
 * @property \CircleLinkHealth\Customer\Entities\User                                                             $patient
 * @property \CircleLinkHealth\Customer\Entities\PatientMonthlySummary[]|\Illuminate\Database\Eloquent\Collection $patientSummaries
 * @property int|null                                                                                             $patient_summaries_count
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection          $revisionHistory
 * @property int|null                                                                                             $revision_history_count
 * @method   static                                                                                               bool|null forceDelete()
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem newModelQuery()
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem newQuery()
 * @method   static                                                                                               \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Problem onlyTrashed()
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem query()
 * @method   static                                                                                               bool|null restore()
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereBillable($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereCcdProblemLogId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereCcdaId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereCpmInstructionId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereCpmProblemId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereCreatedAt($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereDeletedAt($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereIsMonitored($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereName($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem wherePatientId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereProblemImportId($value)
 * @method   static                                                                                               \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\Problem whereUpdatedAt($value)
 * @method   static                                                                                               \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Problem withTrashed()
 * @method   static                                                                                               \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\Problem withoutTrashed()
 * @mixin \Eloquent
 */
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

    public function chargeableServiceCodesForLocation(?int $locationId = null): array
    {
        if ( ! $cpmProblem = $this->cpmProblem) {
            return [];
        }

        $locationId ??= $this->patient->patientInfo->preferred_contact_location;

        if (is_null($locationId)) {
            return [];
        }

        return $cpmProblem->getChargeableServiceCodesForLocation($locationId);
    }

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
