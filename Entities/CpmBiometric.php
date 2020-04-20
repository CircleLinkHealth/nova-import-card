<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmBiometric.
 *
 * @property int                                                                                                                                                                                                                 $id
 * @property int|null                                                                                                                                                                                                            $care_item_id
 * @property string                                                                                                                                                                                                              $name
 * @property int|null                                                                                                                                                                                                            $type
 * @property string                                                                                                                                                                                                              $unit
 * @property \Carbon\Carbon                                                                                                                                                                                                      $created_at
 * @property \Carbon\Carbon                                                                                                                                                                                                      $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBloodPressure|\CircleLinkHealth\SharedModels\Entities\CpmBloodSugar|\CircleLinkHealth\SharedModels\Entities\CpmSmoking|\CircleLinkHealth\SharedModels\Entities\CpmWeight $info
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection                                                                                                                 $carePlanTemplates
 * @property \CircleLinkHealth\SharedModels\Entities\CpmInstruction[]|\Illuminate\Database\Eloquent\Collection                                                                                                                   $cpmInstructions
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection                                                                                                                                 $patient
 * @property \CircleLinkHealth\SharedModels\Entities\CpmBiometricUser[]|\Illuminate\Database\Eloquent\Collection                                                                                                                 $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmBiometric whereUnit($value)
 * @property int|null $care_plan_templates_count
 * @property int|null $cpm_instructions_count
 * @property int|null $patient_count
 * @property int|null $revision_history_count
 * @property int|null $users_count
 */
class CpmBiometric extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use Instructable;

    const BLOOD_PRESSURE = 'Blood Pressure';
    const BLOOD_SUGAR    = 'Blood Sugar';
    const SMOKING        = 'Smoking (# per day)';
    const WEIGHT         = 'Weight';

    protected $fillable = [
        'care_item_id',
        'name',
        'type',
        'unit',
    ];

    protected $table = 'cpm_biometrics';

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
}
