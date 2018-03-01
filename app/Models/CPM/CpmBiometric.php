<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmBiometricService;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmBiometric
 *
 * @property int $id
 * @property int|null $care_item_id
 * @property string $name
 * @property int|null $type
 * @property string $unit
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\CPM\Biometrics\CpmBloodPressure|\App\Models\CPM\Biometrics\CpmBloodSugar|\App\Models\CPM\Biometrics\CpmSmoking|\App\Models\CPM\Biometrics\CpmWeight $info
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanTemplate[] $carePlanTemplates
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmInstruction[] $cpmInstructions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmBiometricUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmBiometric extends \App\BaseModel implements Serviceable
{

    use Instructable;

    const BLOOD_PRESSURE = 'Blood Pressure';
    const BLOOD_SUGAR = 'Blood Sugar';
    const SMOKING = 'Smoking (# per day)';
    const WEIGHT = 'Weight';

    protected $table = 'cpm_biometrics';

    protected $fillable = [
        'care_item_id',
        'name',
        'type'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_biometrics');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    { //I REALLY DON'T THINK THIS IS CORRECT ... RELATIONSHIP should be on "cpm_biometric_id", not "patient_id"
        return $this->belongsToMany(User::class, 'cpm_biometrics_users', 'patient_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function users()
    {
        return $this->belongsToMany(CpmBiometricUser::class, 'cpm_biometrics_users', 'cpm_biometric_id');
    }

    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmBiometricService();
    }
}
