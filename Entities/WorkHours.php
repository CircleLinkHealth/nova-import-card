<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Customer\Entities\WorkHours.
 *
 * @property int                                           $id
 * @property string                                        $workhourable_type
 * @property int                                           $workhourable_id
 * @property int                                           $monday
 * @property int                                           $tuesday
 * @property int                                           $wednesday
 * @property int                                           $thursday
 * @property int                                           $friday
 * @property int                                           $saturday
 * @property int                                           $sunday
 * @property \Carbon\Carbon|null                           $created_at
 * @property \Carbon\Carbon|null                           $updated_at
 * @property string|null                                   $deleted_at
 * @property \Eloquent|\Illuminate\Database\Eloquent\Model $workhourable
 * @method   static                                        bool|null forceDelete()
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Models\WorkHours onlyTrashed()
 * @method   static                                        bool|null restore()
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereCreatedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereDeletedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereFriday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereMonday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereSaturday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereSunday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereThursday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereTuesday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereUpdatedAt($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWednesday($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWorkhourableId($value)
 * @method   static                                        \Illuminate\Database\Eloquent\Builder|\App\Models\WorkHours whereWorkhourableType($value)
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Models\WorkHours withTrashed()
 * @method   static                                        \Illuminate\Database\Query\Builder|\App\Models\WorkHours withoutTrashed()
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours query()
 * @property int|null                                                                                    $revision_history_count
 * @property \Illuminate\Support\Carbon                                                                  $work_week_start
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\WorkHours whereWorkWeekStart($value)
 */
class WorkHours extends \CircleLinkHealth\Core\Entities\BaseModel
{
    use SoftDeletes;

    protected $dates = [
        'work_week_start',
    ];

    protected $fillable = [
        'workhourable_type',
        'workhourable_id',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'work_week_start',
    ];

    /**
     * Get all of the owning workhourable models.
     */
    public function workhourable()
    {
        return $this->morphTo();
    }
}
