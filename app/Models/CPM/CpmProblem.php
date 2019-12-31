<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Importer\Models\ImportedItems\ProblemImport;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmProblem.
 *
 * @property int                                                                                 $id
 * @property string                                                                              $default_icd_10_code
 * @property string                                                                              $name
 * @property string                                                                              $icd10from
 * @property string                                                                              $icd10to
 * @property float                                                                               $icd9from
 * @property float                                                                               $icd9to
 * @property string                                                                              $contains
 * @property \Carbon\Carbon                                                                      $created_at
 * @property \Carbon\Carbon                                                                      $updated_at
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                    $carePlanTemplates
 * @property \App\Models\CPM\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection             $cpmBiometricsToBeActivated
 * @property \App\Models\CPM\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection           $cpmInstructions
 * @property \App\Models\CPM\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection             $cpmLifestylesToBeActivated
 * @property \App\Models\CPM\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection       $cpmMedicationGroupsToBeActivated
 * @property \App\Models\CPM\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection               $cpmSymptomsToBeActivated
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $patient
 * @property App\Models\CPM\CpmInstructable                                                      $instructable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereContains($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereDefaultIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10from($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10to($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9from($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9to($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int                                                                                         $is_behavioral
 * @property int                                                                                         $weight
 * @property \App\Importer\Models\ImportedItems\ProblemImport[]|\Illuminate\Database\Eloquent\Collection $problemImports
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]              $revisionHistory
 * @property \App\CLH\CCD\Importer\SnomedToCpmIcdMap[]|\Illuminate\Database\Eloquent\Collection          $snomedMaps
 * @property \App\Models\CPM\CpmProblemUser[]|\Illuminate\Database\Eloquent\Collection                   $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIsBehavioral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem withIcd10Codes()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem withLatestCpmInstruction()
 * @property int|null $care_plan_templates_count
 * @property int|null $cpm_biometrics_to_be_activated_count
 * @property int|null $cpm_instructions_count
 * @property int|null $cpm_lifestyles_to_be_activated_count
 * @property int|null $cpm_medication_groups_to_be_activated_count
 * @property int|null $cpm_symptoms_to_be_activated_count
 * @property int|null $patient_count
 * @property int|null $problem_imports_count
 * @property int|null $revision_history_count
 * @property int|null $snomed_maps_count
 * @property int|null $user_count
 */
class CpmProblem extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    protected $guarded = [];

    protected $table = 'cpm_problems';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_problems');
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmBiometricsToBeActivated()
    {
        return $this->belongsToMany(CpmBiometric::class, 'cpm_problems_activate_cpm_biometrics')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestylesToBeActivated()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'cpm_problems_activate_cpm_lifestyles')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroupsToBeActivated()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'cpm_problems_activate_cpm_medication_groups')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptomsToBeActivated()
    {
        return $this->belongsToMany(CpmSymptom::class, 'cpm_problems_activate_cpm_symptoms')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    public function instructable()
    {
        return $this->hasOne(CpmInstructable::class, 'instructable_id');
    }

    public function instruction()
    {
        return $this->cpmInstructions()->first();
    }

    public function instructions()
    {
        return $this->user()->whereNotNull('cpm_instruction_id')->with(['instruction'])->groupBy('cpm_instruction_id');
    }

    public function isDuplicateOf($name)
    {
        return $this->where('contains', 'LIKE', "%${name}%");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_problems_users', 'patient_id');
    }

    public function problemImports()
    {
        return $this->hasMany(ProblemImport::class);
    }

    public function scopeWithIcd10Codes($builder)
    {
        return $builder->with(['snomedMaps' => function ($q) {
            return $q->whereNotNull('icd_10_name')->where('icd_10_name', '!=', '')->distinct('icd_10_name')->groupBy('icd_10_name', 'snomed_to_cpm_icd_maps.id');
        }]);
    }

    public function scopeWithLatestCpmInstruction($builder)
    {
        return $builder->with(['cpmInstructions' => function ($q) {
            return $q->latest();
        }]);
    }

    public function snomedMaps()
    {
        return $this->hasMany(SnomedToCpmIcdMap::class);
    }

    public function user()
    {
        return $this->hasMany(CpmProblemUser::class, 'cpm_problem_id');
    }
}
