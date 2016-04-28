<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmMisc extends Model {
    
    use Instructable;

    const ALLERGIES = 'Allergies';
    const APPOINTMENTS = 'Appointments';
    const MEDICATION_LIST = 'Medication List';
    const OLD_MEDS_LIST = 'Old Meds List';
    const OTHER = 'Other';
    const OTHER_CONDITIONS = 'Other Conditions';
    const SOCIAL_SERVICES = 'Social Services';
    const TRACK_CARE_TRANSITIONS = 'Track Care Transitions';

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_miscs');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_miscs_users');
    }

}
