<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\EmrDirectAddress.
 *
 * @property int                                           $id
 * @property string                                        $emrDirectable_type
 * @property int                                           $emrDirectable_id
 * @property string                                        $address
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $emrDirectable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereEmrDirectableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereEmrDirectableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmrDirectAddress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmrDirectAddress extends \App\BaseModel
{
    public $fillable = [
        'emrDirectable_type',
        'emrDirectable_id',
        'address',
    ];

    /**
     * Get all of the owning contactCardable models.
     */
    public function emrDirectable()
    {
        return $this->morphTo();
    }
}
