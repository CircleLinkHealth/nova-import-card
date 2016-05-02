<?php namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanItem;
use App\CarePlanTemplate;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmLifestyle extends Model
{
    
    use Instructable;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_lifestyles');
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
        return $this->belongsToMany(User::class, 'cpm_lifestyles_users', 'patient_id');
    }
}
