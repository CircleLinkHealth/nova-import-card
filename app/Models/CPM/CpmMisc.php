<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmMiscService;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmMisc extends Model implements Serviceable{
    
    use Instructable;

    const ALLERGIES = 'Allergies';
    const APPOINTMENTS = 'Appointments';
    const MEDICATION_LIST = 'Medication List';
    const OLD_MEDS_LIST = 'Old Meds List';
    const OTHER = 'Other';
    const OTHER_CONDITIONS = 'Conditions List';
    const SOCIAL_SERVICES = 'Social Services';
    const TRACK_CARE_TRANSITIONS = 'Track Care Transitions';

    protected $fillable = [
        'details_care_item_id',
        'care_item_id',
        'name'
    ];

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
        return $this->belongsToMany(User::class, 'cpm_miscs_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmMiscService();
    }
}
