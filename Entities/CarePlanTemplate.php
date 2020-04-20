<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\Practice;

/**
 * CircleLinkHealth\SharedModels\Entities\CarePlanTemplate.
 *
 * @property int                                                                                                   $id
 * @property string                                                                                                $display_name
 * @property int|null                                                                                              $program_id
 * @property string                                                                                                $type
 * @property \Carbon\Carbon                                                                                        $created_at
 * @property \Carbon\Carbon                                                                                        $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometric[]|\Illuminate\Database\Eloquent\Collection       $cpmBiometrics
 * @property \CircleLinkHealth\SharedModels\Entities\CpmLifestyle[]|\Illuminate\Database\Eloquent\Collection       $cpmLifestyles
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMedicationGroup[]|\Illuminate\Database\Eloquent\Collection $cpmMedicationGroups
 * @property \CircleLinkHealth\SharedModels\Entities\CpmMisc[]|\Illuminate\Database\Eloquent\Collection            $cpmMiscs
 * @property \CircleLinkHealth\SharedModels\Entities\CpmProblem[]|\Illuminate\Database\Eloquent\Collection         $cpmProblems
 * @property \CircleLinkHealth\SharedModels\Entities\CpmSymptom[]|\Illuminate\Database\Eloquent\Collection         $cpmSymptoms
 * @property \CircleLinkHealth\Customer\Entities\Practice|null                                                     $program
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanTemplate query()
 * @property int|null $cpm_biometrics_count
 * @property int|null $cpm_lifestyles_count
 * @property int|null $cpm_medication_groups_count
 * @property int|null $cpm_miscs_count
 * @property int|null $cpm_problems_count
 * @property int|null $cpm_symptoms_count
 * @property int|null $revision_history_count
 */
class CarePlanTemplate extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = ['program_id', 'display_name', 'type'];

    // CPM Entities

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmBiometrics()
    {
        return $this->belongsToMany(CpmBiometric::class, 'care_plan_templates_cpm_biometrics')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestyles()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'care_plan_templates_cpm_lifestyles')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroups()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'care_plan_templates_cpm_medication_groups')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMiscs()
    {
        return $this->belongsToMany(CpmMisc::class, 'care_plan_templates_cpm_miscs')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmProblems()
    {
        return $this->belongsToMany(CpmProblem::class, 'care_plan_templates_cpm_problems')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptoms()
    {
        return $this->belongsToMany(CpmSymptom::class, 'care_plan_templates_cpm_symptoms')
            ->withPivot('has_instruction')
            ->withPivot('cpm_instruction_id')
            ->withPivot('page')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * Get a cpm***** relationship with it's related instructions, ordered using db field ui_config.
     *
     * @param mixed $relationship
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function loadWithInstructionsAndSort($relationship)
    {
        if (empty($relationship)) {
            return false;
        }
        if ( ! is_array($relationship)) {
            $relationship = (array) $relationship;
        }

        foreach ($relationship as $rel) {
            if ( ! method_exists($this, $rel)) {
                throw new \Exception("Relationship `${rel}` does not exist.");
            }

            $attributes[$rel] = function ($query) use ($rel) {
                $query->with('cpmInstructions')
                    ->orderBy('pivot_ui_sort');
            };
        }

        return $this->load($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Practice::class, 'program_id');
    }
}
