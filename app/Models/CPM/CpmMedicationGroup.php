<?php namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Models\CCD\Medication;
use App\Services\CPM\CpmMedicationGroupService;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmMedicationGroup extends Model implements Serviceable
{
    
    use Instructable;

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
        return $this->hasMany(Medication::class);
    }
    
    public function carePlanItemIdDeprecated()
    {
        return $this->belongsTo(CareItem::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_medication_groups_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmMedicationGroupService();
    }
}
