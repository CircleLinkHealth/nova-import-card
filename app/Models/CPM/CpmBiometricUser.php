<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\User;
use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;

/**
 * App\Models\CPM\CpmBiometric
 *
 * @property int $id
 * @property int|null $cpm_instruction_id
 * @property int $cpm_biometric_id
 * @property int $patient_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $patient
 * @property-read \App\Models\CPM\CpmBiometric $biometric
 * @property-read \App\Models\CPM\CpmInstruction $instruction
 * @property-read \App\Models\CPM\Biometrics\CpmBloodPressure $bloodPressure
 * @property-read \App\Models\CPM\Biometrics\CpmBloodSugar $bloodSugar
 * @property-read \App\Models\CPM\Biometrics\CpmBloodSmoking $smoking
 * @property-read \App\Models\CPM\Biometrics\CpmBloodWeight $weight
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmBiometricUser extends \App\BaseModel
{
    protected $table = 'cpm_biometrics_users';
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function patient()
    {
        return $this->hasOne(User::class, 'patient_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function biometric()
    {
        return $this->belongsTo(CpmBiometric::class, 'cpm_biometric_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function instruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function bloodPressure()
    {
        return $this->hasOne(CpmBloodPressure::class, 'patient_id', 'patient_id');
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function bloodSugar()
    {
        return $this->hasOne(CpmBloodSugar::class, 'patient_id', 'patient_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function smoking()
    {
        return $this->hasOne(CpmSmoking::class, 'patient_id', 'patient_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function weight()
    {
        return $this->hasOne(CpmWeight::class, 'patient_id', 'patient_id');
    }
}
