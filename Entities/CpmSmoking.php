<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Contracts\Models\CPM\Biometric;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmSmoking.
 *
 * @property int                                      $id
 * @property int                                      $patient_id
 * @property string                                   $starting
 * @property string                                   $target
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereCreatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking wherePatientId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereStarting($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereTarget($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmSmoking query()
 * @property int|null                                                                                    $revision_history_count
 */
class CpmSmoking extends \CircleLinkHealth\Core\Entities\BaseModel implements Biometric
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
        $user->loadMissing('cpmSmoking');
        $biometric = $user->cpmSmoking;

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
