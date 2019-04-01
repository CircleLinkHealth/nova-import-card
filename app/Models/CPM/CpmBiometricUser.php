<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

use App\Models\CPM\Biometrics\CpmBloodPressure;
use App\Models\CPM\Biometrics\CpmBloodSugar;
use App\Models\CPM\Biometrics\CpmSmoking;
use App\Models\CPM\Biometrics\CpmWeight;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\CpmBiometric.
 *
 * @property int                                         $id
 * @property int|null                                    $cpm_instruction_id
 * @property int                                         $cpm_biometric_id
 * @property int                                         $patient_id
 * @property \Carbon\Carbon                              $created_at
 * @property \Carbon\Carbon                              $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User    $patient
 * @property \App\Models\CPM\CpmBiometric                $biometric
 * @property \App\Models\CPM\CpmInstruction              $instruction
 * @property \App\Models\CPM\Biometrics\CpmBloodPressure $bloodPressure
 * @property \App\Models\CPM\Biometrics\CpmBloodSugar    $bloodSugar
 * @property \App\Models\CPM\Biometrics\CpmBloodSmoking  $smoking
 * @property \App\Models\CPM\Biometrics\CpmBloodWeight   $weight
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmBiometricId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser wherePatientId($value)
 */
class CpmBiometricUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $table = 'cpm_biometrics_users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function biometric()
    {
        return $this->belongsTo(CpmBiometric::class, 'cpm_biometric_id');
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }

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
