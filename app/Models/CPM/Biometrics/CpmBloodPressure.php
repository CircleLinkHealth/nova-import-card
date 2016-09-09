<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBloodPressure extends Model implements Biometric
{

    protected $fillable = [
        'patient_id',
        'starting',
        'target',
        'systolic_high_alert',
        'systolic_low_alert',
        'diastolic_high_alert',
        'diastolic_low_alert',
    ];

    protected $attributes = [
        'systolic_high_alert' => 180,
        'systolic_low_alert' => 80,
        'diastolic_high_alert' => 90,
        'diastolic_low_alert' => 40,
        'target' => '130/80',
    ];

    public static $rules = [
        'systolic_high_alert' => 'max:999|numeric',
        'systolic_low_alert' => 'max:999|numeric',
        'diastolic_high_alert' => 'max:999|numeric',
        'diastolic_low_alert' => 'max:999|numeric',
        'target' => 'max:7',
    ];

    public static $messages = [
        'systolic_high_alert.max' => 'The Systolic Blood Pressure High Alert may not be greater than 999.',
        'systolic_low_alert.max' => 'The Systolic Blood Pressure Low Alert may not be greater than 999.',
        'diastolic_high_alert.max' => 'The Diastolic Blood Pressure High Alert may not be greater than 999.',
        'diastolic_low_alert.max' => 'The Diastolic Blood Pressure Low Alert may not be greater than 999.',
        'target.max' => 'The Target Blood Pressure may not be greater than 7 characters.',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function getUserValues(User $user)
    {
        $biometric = $this->wherePatientId($user->ID)->first();
        return $biometric
            ? [
                'starting' => $biometric->starting,
                'target' => $biometric->target
            ]
            : false;
    }

}
