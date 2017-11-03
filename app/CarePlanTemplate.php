<?php namespace App;

use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmLifestyle;
use App\Models\CPM\CpmMedicationGroup;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmSymptom;
use Illuminate\Database\Eloquent\Model;

class CarePlanTemplate extends \App\BaseModel
{
    protected $fillable = ['program_id', 'display_name', 'type'];

    /*
     *
     * CPM Entities
     *
     */

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(Practice::class, 'program_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function careplan()
    {
        return $this->hasOne('App\PatientCarePlan', 'care_plan_template_id');
    }


    /**
     * Get a cpm***** relationship with it's related instructions, ordered using db field ui_config
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function loadWithInstructionsAndSort($relationship)
    {
        if (empty($relationship)) {
            return false;
        }
        if (!is_array($relationship)) {
            $relationship = (array)$relationship;
        }

        foreach ($relationship as $rel) {
            if (!method_exists($this, $rel)) {
                throw new \Exception("Relationship `$rel` does not exist.");
            }

            $attributes[$rel] = function ($query) use ($rel) {
                $query->with('cpmInstructions')
                    ->orderBy('pivot_ui_sort');
            };
        }

        return $this->load($attributes);
    }
}
