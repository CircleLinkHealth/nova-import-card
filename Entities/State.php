<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

/**
 * CircleLinkHealth\Customer\Entities\State.
 *
 * @property int                                                                                  $id
 * @property string                                                                               $name
 * @property string                                                                               $code
 * @property \CircleLinkHealth\Customer\Entities\Nurse[]|\Illuminate\Database\Eloquent\Collection $nurses
 * @method   static                                                                               \Illuminate\Database\Eloquent\Builder|\App\State whereCode($value)
 * @method   static                                                                               \Illuminate\Database\Eloquent\Builder|\App\State whereId($value)
 * @method   static                                                                               \Illuminate\Database\Eloquent\Builder|\App\State whereName($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\State query()
 * @property int|null                                                                                    $nurses_count
 * @property int|null                                                                                    $revision_history_count
 */
class State extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    public function nurses()
    {
        return $this->belongsToMany(Nurse::class);
    }
}
