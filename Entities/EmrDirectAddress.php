<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

/**
 * CircleLinkHealth\Customer\Entities\EmrDirectAddress.
 *
 * @property int                                           $id
 * @property string                                        $emrDirectable_type
 * @property int                                           $emrDirectable_id
 * @property string                                        $address
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $emrDirectable
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereEmrDirectableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereEmrDirectableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EmrDirectAddress query()
 * @property int|null $revision_history_count
 */
class EmrDirectAddress extends \CircleLinkHealth\Core\Entities\BaseModel
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
