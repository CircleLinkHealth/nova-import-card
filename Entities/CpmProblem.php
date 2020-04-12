<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_problems_users', 'patient_id');
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
