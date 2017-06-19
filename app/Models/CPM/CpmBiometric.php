<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmBiometricService;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBiometric extends Model implements Serviceable
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
    {
        return $this->belongsToMany(User::class, 'cpm_biometrics_users', 'patient_id');
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
