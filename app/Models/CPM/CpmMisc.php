<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmMiscService;
use App\Models\CPM\CpmMiscUser;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmMisc
 *
 * @property int $id
 * @property int|null $details_care_item_id
 * @property int|null $care_item_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanTemplate[] $carePlanTemplates
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmInstruction[] $cpmInstructions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmMiscUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereDetailsCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmMisc extends \App\BaseModel implements Serviceable
{
    use Instructable;

    const ALLERGIES = 'Allergies';
    const APPOINTMENTS = 'Appointments';
    const MEDICATION_LIST = 'Medication List';
    const OLD_MEDS_LIST = 'Old Meds List';
    const OTHER = 'Other';
    const OTHER_CONDITIONS = 'Full Conditions List';
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

    public function users()
    {
        return $this->hasMany(CpmMiscUser::class, 'cpm_misc_id');
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
