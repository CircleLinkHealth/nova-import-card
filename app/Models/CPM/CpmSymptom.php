<?php namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmSymptomService;
use App\Models\CPM\CpmSymptomUser;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmSymptom
 *
 * @property int $id
 * @property int|null $care_item_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\CareItem $carePlanItemIdDeprecated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanTemplate[] $carePlanTemplates
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmInstruction[] $cpmInstructions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmSymptomUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptom whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmSymptom extends \App\BaseModel implements Serviceable
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

    public function users()
    {
        return $this->belongsToMany(CpmSymptomUser::class);
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
