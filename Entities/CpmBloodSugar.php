<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Contracts\Models\CPM\Biometric;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmBloodSugar.
 *
 * @property int                                      $id
 * @property int                                      $patient_id
 * @property string                                   $starting
 * @property string                                   $target
 * @property string                                   $starting_a1c
 * @property string                                   $high_alert
 * @property string                                   $low_alert
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereCreatedAt($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereHighAlert($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereLowAlert($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar wherePatientId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereStarting($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereStartingA1c($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereTarget($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmBloodSugar query()
 * @property int|null                                                                                    $revision_history_count
 */
class CpmBloodSugar extends \CircleLinkHealth\Core\Entities\BaseModel implements Biometric
{
    public static $messages = [
        'starting.max'     => 'The Starting Blood Sugar may not be greater than 3 characters.',
        'target.max'       => 'The Target Blood Sugar may not be greater than 3 characters.',
        'starting_a1c.max' => 'The Starting A1c Blood Sugar may not be greater than 3 characters.',
        'high_alert.max'   => 'The High Alert Blood Sugar may not be greater than 3 characters.',
        'low_alert.max'    => 'The Low Alert Blood Sugar may not be greater than 3 characters.',
    ];
    public static $rules = [
        'starting'     => 'max:999|numeric',
        'target'       => 'max:999|numeric',
        'starting_a1c' => 'max:999|numeric',
        'high_alert'   => 'max:999|numeric',
        'low_alert'    => 'max:999|numeric',
    ];
    protected $attributes = [
        'target'     => 120,
        'high_alert' => 350,
        'low_alert'  => 60,
    ];
    protected $fillable = [
        'patient_id',
        'starting',
        'target',
        'starting_a1c',
        'high_alert',
        'low_alert',
    ];

    public function biometric()
    {
        return CpmBiometric::where('name', 'LIKE', '%sugar%');
    }

    public function getUserValues(User $user)
    {
        $user->loadMissing('cpmBloodSugar');
        $biometric = $user->cpmBloodSugar;

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
