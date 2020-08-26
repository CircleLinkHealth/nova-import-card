<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmProblem.
 *
 * @property int                                                                                                              $id
 * @property string|null                                                                                                      $default_icd_10_code
 * @property string                                                                                                           $name
 * @property string                                                                                                           $icd10from
 * @property string                                                                                                           $icd10to
 * @property float                                                                                                            $icd9from
 * @property float                                                                                                            $icd9to
 * @property string                                                                                                           $contains
 * @property int                                                                                                              $is_behavioral
 * @property int                                                                                                              $weight
 * @property \Illuminate\Support\Carbon                                                                                       $created_at
 * @property \Illuminate\Support\Carbon                                                                                       $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection              $carePlanTemplates
 * @property int|null                                                                                                         $care_plan_templates_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection                  $cpmBiometricsToBeActivated
 * @property int|null                                                                                                         $cpm_biometrics_to_be_activated_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection                $cpmInstructions
 * @property int|null                                                                                                         $cpm_instructions_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection                  $cpmLifestylesToBeActivated
 * @property int|null                                                                                                         $cpm_lifestyles_to_be_activated_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection            $cpmMedicationGroupsToBeActivated
 * @property int|null                                                                                                         $cpm_medication_groups_to_be_activated_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection                    $cpmSymptomsToBeActivated
 * @property int|null                                                                                                         $cpm_symptoms_to_be_activated_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstructable                                                          $instructable
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                              $patient
 * @property int|null                                                                                                         $patient_count
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection                      $revisionHistory
 * @property int|null                                                                                                         $revision_history_count
 * @property \CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap[]|\Illuminate\Database\Eloquent\Collection $snomedMaps
 * @property int|null                                                                                                         $snomed_maps_count
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblemUser[]|\Illuminate\Database\Eloquent\Collection                $user
 * @property int|null                                                                                                         $user_count
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem newModelQuery()
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem newQuery()
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem query()
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereContains($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereCreatedAt($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereDefaultIcd10Code($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereIcd10from($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereIcd10to($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereIcd9from($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereIcd9to($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereId($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereIsBehavioral($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereName($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereUpdatedAt($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem whereWeight($value)
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem withIcd10Codes()
 * @method   static                                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CpmProblem withLatestCpmInstruction()
 * @mixin \Eloquent
 */
class CpmProblem extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    const DIABETES_TYPE_1 = 'Diabetes Type 1';

    const DIABETES_TYPE_2 = 'Diabetes Type 2';

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

    public function locationChargeableServices()
    {
        return $this->belongsToMany(ChargeableService::class, 'location_problem_services', 'cpm_problem_id', 'chargeable_service_id')
            ->withPivot(['location_id']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_problems_users', 'patient_id');
    }

    public function scopeWithChargeableServicesForLocation($query, $locationId)
    {
        return $query->with(['locationChargeableServices' => function ($lps) use ($locationId) {
            $lps->where('pivot.location_id', $locationId);
        }]);
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
