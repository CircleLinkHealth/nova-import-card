<?php namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmSymptomService;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmSymptom extends Model implements Serviceable
{
    
    use Instructable;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_symptoms');
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
        return $this->belongsToMany(User::class, 'cpm_symptoms_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmSymptomService();
    }
}
