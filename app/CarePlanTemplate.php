<?php namespace App;

use App\Models\CPM\CpmBiometric;
use App\Models\CPM\CpmLifestyle;
use App\Models\CPM\CpmMedicationGroup;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmProblem;
use App\Models\CPM\CpmSymptom;
use Illuminate\Database\Eloquent\Model;

class CarePlanTemplate extends Model {

    const CLH_DEFAULT = 'CLH Default';

    protected $fillable = ['program_id', 'display_name'];

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
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestyles()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'care_plan_templates_cpm_lifestyles')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroups()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'care_plan_templates_cpm_medication_groups')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMiscs()
    {
        return $this->belongsToMany(CpmMisc::class, 'care_plan_templates_cpm_miscs')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmProblems()
    {
        return $this->belongsToMany(CpmProblem::class, 'care_plan_templates_cpm_problems')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptoms()
    {
        return $this->belongsToMany(CpmSymptom::class, 'care_plan_templates_cpm_symptoms')
            ->withPivot('ui_sort')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo('App\WpBlog', 'program_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function careplan()
    {
        return $this->hasOne('App\PatientCarePlan','care_plan_template_id');
    }

}
