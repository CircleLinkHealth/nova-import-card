<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CarePlanModels\Entities;

use CircleLinkHealth\CarePlanModels\Entities\CpmBloodPressure;
use CircleLinkHealth\CarePlanModels\Entities\CpmBloodSugar;
use CircleLinkHealth\CarePlanModels\Entities\CpmSmoking;
use CircleLinkHealth\CarePlanModels\Entities\CpmWeight;
use CircleLinkHealth\CarePlanModels\Entities\CpmBiometric;
use CircleLinkHealth\CarePlanModels\Entities\CpmInstruction;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CarePlanModels\Entities\CpmBiometric.
 *
 * @property int                                         $id
 * @property int|null                                    $cpm_instruction_id
 * @property int                                         $cpm_biometric_id
 * @property int                                         $patient_id
 * @property \Carbon\Carbon                              $created_at
 * @property \Carbon\Carbon                              $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User    $patient
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmBiometric                $biometric
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmInstruction              $instruction
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmBloodPressure $bloodPressure
 * @property \CircleLinkHealth\CarePlanModels\Entities\CpmBloodSugar    $bloodSugar
 * @property \App\Models\CPM\Biometrics\CpmBloodSmoking  $smoking
 * @property \App\Models\CPM\Biometrics\CpmBloodWeight   $weight
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\CarePlanModels\Entities\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmBiometricId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser whereCpmInstructionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometricUser wherePatientId($value)
 * @property int|null $revision_history_count
 */
class CpmBiometricUser extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];
    protected $table   = 'cpm_biometrics_users';

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
