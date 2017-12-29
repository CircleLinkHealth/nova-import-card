<?php namespace App\Models\CPM;

use App\CarePlanTemplate;
use App\User;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmBiometric extends \App\BaseModel implements Serviceable
{

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
        return $this->hasOne(CpmBiometric::class, 'cpm_biometric_id');
    }
    
    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function instruction()
    {
        return $this->hasOne(CpmInstruction::class, 'cpm_instruction_id');
    }
}
