<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\Models\CPM\CpmBiometric;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\CPM\Biometrics\CpmBloodPressure.
 *
 * @property int            $id
 * @property int            $patient_id
 * @property string         $starting
 * @property string         $target
 * @property string         $systolic_high_alert
 * @property string         $systolic_low_alert
 * @property string         $diastolic_high_alert
 * @property string         $diastolic_low_alert
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User      $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereDiastolicHighAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereDiastolicLowAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereStarting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereSystolicHighAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereSystolicLowAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodPressure whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmBloodPressure extends \CircleLinkHealth\Core\Entities\BaseModel implements Biometric
{
    public static $messages = [
        'systolic_high_alert.max'  => 'The Systolic Blood Pressure High Alert may not be greater than 999.',
        'systolic_low_alert.max'   => 'The Systolic Blood Pressure Low Alert may not be greater than 999.',
        'diastolic_high_alert.max' => 'The Diastolic Blood Pressure High Alert may not be greater than 999.',
        'diastolic_low_alert.max'  => 'The Diastolic Blood Pressure Low Alert may not be greater than 999.',
        'target.max'               => 'The Target Blood Pressure may not be greater than 7 characters.',
    ];

    public static $rules = [
        'systolic_high_alert'  => 'max:999|numeric',
        'systolic_low_alert'   => 'max:999|numeric',
        'diastolic_high_alert' => 'max:999|numeric',
        'diastolic_low_alert'  => 'max:999|numeric',
        'target'               => 'max:7',
    ];
    protected $attributes = [
        'systolic_high_alert'  => 180,
        'systolic_low_alert'   => 80,
        'diastolic_high_alert' => 90,
        'diastolic_low_alert'  => 40,
        'target'               => '130/80',
    ];
    protected $fillable = [
        'patient_id',
        'starting',
        'target',
        'systolic_high_alert',
        'systolic_low_alert',
        'diastolic_high_alert',
        'diastolic_low_alert',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setDefaultTarget();
    }

    public function biometric()
    {
        return CpmBiometric::where('name', 'LIKE', '%pressure%');
    }

    public function getUserValues(User $user)
    {
        $biometric = $this->wherePatientId($user->id)->first();

        return $biometric
            ? [
                'starting' => $biometric->starting,
                'target'   => $biometric->target,
            ]
            : false;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function setDefaultTarget()
    {
        $patient = $this->patient;

        if ( ! $patient) {
            return;
        }

        $settings = $this->patient->primaryPractice->settings->first();

        if ( ! $settings) {
            return;
        }

        $this->attributes['target'] = $settings->default_target_bp;
    }
}
