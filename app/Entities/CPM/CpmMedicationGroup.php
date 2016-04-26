<?php namespace App\Entities\CPM;

use App\CarePlanTemplate;
use App\Entities\CCD\CcdMedication;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmMedicationGroup extends Model {

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_medication_groups');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function medications()
    {
        return $this->hasMany(CcdMedication::class);
    }
}
