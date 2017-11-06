<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmBloodSugar extends \App\BaseModel implements Biometric
{

    public static $rules = [
        'starting' => 'max:999|numeric',
        'target' => 'max:999|numeric',
        'starting_a1c' => 'max:999|numeric',
        'high_alert' => 'max:999|numeric',
        'low_alert' => 'max:999|numeric',
    ];
    public static $messages = [
        'starting.max' => 'The Starting Blood Sugar may not be greater than 3 characters.',
        'target.max' => 'The Target Blood Sugar may not be greater than 3 characters.',
        'starting_a1c.max' => 'The Starting A1c Blood Sugar may not be greater than 3 characters.',
        'high_alert.max' => 'The High Alert Blood Sugar may not be greater than 3 characters.',
        'low_alert.max' => 'The Low Alert Blood Sugar may not be greater than 3 characters.',
    ];
    protected $fillable = [
        'patient_id',
        'starting',
        'target',
        'starting_a1c',
        'high_alert',
        'low_alert',
    ];
    protected $attributes = [
        'target'     => 120,
        'high_alert' => 350,
        'low_alert'  => 60,
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
        $biometric = $this->wherePatientId($user->id)->first();

        return $biometric
            ? [
                'starting' => $biometric->starting,
                'target' => $biometric->target
            ]
            : false;
    }
}
