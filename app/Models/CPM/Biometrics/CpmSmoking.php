<?php namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\User;
use Illuminate\Database\Eloquent\Model;

class CpmSmoking extends \App\BaseModel implements Biometric
{

    public static $rules = [
        'starting' => 'max:999|numeric',
        'target' => 'max:999|numeric',
    ];
    public static $messages = [
        'starting.max' => 'The Starting Blood Sugar may not be greater than 999.',
        'target.max' => 'The Target Blood Sugar may not be greater than 999.',
    ];
    protected $fillable = [
        'patient_id',
        'starting',
        'target',
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
