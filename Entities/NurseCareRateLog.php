<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\TimeTracking\Entities\Activity;

/**
 * CircleLinkHealth\Customer\Entities\NurseCareRateLog.
 *
 * @property int                                                   $id
 * @property int                                                   $nurse_id
 * @property int|null                                              $activity_id
 * @property string                                                $ccm_type
 * @property int                                                   $increment
 * @property \Carbon\Carbon|null                                   $created_at
 * @property \Carbon\Carbon|null                                   $updated_at
 * @property \CircleLinkHealth\TimeTracking\Entities\Activity|null $activity
 * @property \CircleLinkHealth\Customer\Entities\Nurse             $nurse
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCcmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereIncrement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereNurseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog query()
 * @property-read int|null $revision_history_count
 */
class NurseCareRateLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = ['nurse_id', 'activity_id', 'ccm_type', 'increment', 'created_at'];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
}
