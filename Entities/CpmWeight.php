<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Contracts\Models\CPM\Biometric;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\SharedModels\Entities\CpmWeight.
 *
 * @property int                                      $id
 * @property int                                      $patient_id
 * @property string                                   $starting
 * @property string                                   $target
 * @property int                                      $monitor_changes_for_chf
 * @property \Carbon\Carbon                           $created_at
 * @property \Carbon\Carbon                           $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereCreatedAt($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereId($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereMonitorChangesForChf($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight wherePatientId($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereStarting($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereTarget($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight newModelQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight newQuery()
 * @method static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\Biometrics\CpmWeight query()
 * @property int|null                                                                                    $revision_history_count
 */
class CpmWeight extends \CircleLinkHealth\Core\Entities\BaseModel implements Biometric
{
    public static $messages = [
        'starting.max' => 'The Starting Weight may not be greater than 999.',
        'target.max'   => 'The Target Weight may not be greater than 999.',
    ];
    public static $rules = [
        'starting' => 'max:999|numeric',
        'target'   => 'max:999|numeric',
    ];
    protected $fillable = [
        'monitor_changes_for_chf',
        'patient_id',
        'starting',
        'target',
    ];

    public function biometric()
    {
        return CpmBiometric::where('name', 'LIKE', '%weight%');
    }

    public function getUserValues(User $user)
    {
        $user->loadMissing('cpmWeight');
        $biometric = $user->cpmWeight;

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
