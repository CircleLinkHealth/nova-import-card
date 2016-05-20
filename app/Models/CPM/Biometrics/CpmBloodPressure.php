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
        'systolic_high_alert' => 181,
        'systolic_low_alert' => 80,
        'diastolic_high_alert' => 90,
        'diastolic_low_alert' => 40,
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
