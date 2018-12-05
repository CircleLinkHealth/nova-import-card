<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM\Biometrics;

use App\Contracts\Models\CPM\Biometric;
use App\Models\CPM\CpmBiometric;
use App\User;

/**
 * App\Models\CPM\Biometrics\CpmSmoking.
 *
 * @property int            $id
 * @property int            $patient_id
 * @property string         $starting
 * @property string         $target
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\User      $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereStarting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmSmoking extends \App\BaseModel implements Biometric
{
    public static $messages = [
        'starting.max' => 'The Starting Blood Sugar may not be greater than 999.',
        'target.max'   => 'The Target Blood Sugar may not be greater than 999.',
    ];
    public static $rules = [
        'starting' => 'max:999|numeric',
        'target'   => 'max:999|numeric',
    ];
    protected $fillable = [
        'patient_id',
        'starting',
        'target',
    ];

    public function biometric()
    {
        return CpmBiometric::where('name', 'LIKE', '%smoking%');
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
}
