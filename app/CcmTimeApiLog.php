<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\TimeTracking\Entities\Activity;

/**
 * App\CcmTimeApiLog.
 *
 * @property int                                              $id
 * @property int                                              $activity_id
 * @property \Carbon\Carbon                                   $created_at
 * @property \Carbon\Carbon                                   $updated_at
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity $activity
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CcmTimeApiLog query()
 */
class CcmTimeApiLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $guarded = [];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
