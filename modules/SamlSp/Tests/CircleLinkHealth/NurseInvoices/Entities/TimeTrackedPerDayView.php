<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\NurseInvoices\Entities\TimeTrackedPerDayView.
 *
 * @property float|null  $total_time
 * @property string|null $date
 * @property int         $user_id
 * @property bool        $is_billable
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView newModelQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView newQuery()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView query()
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView whereDate($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView whereIsBillable($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView whereTotalTime($value)
 * @method   static      \Illuminate\Database\Eloquent\Builder|\App\TimeTrackedPerDayView whereUserId($value)
 * @mixin \Eloquent
 */
class TimeTrackedPerDayView extends Model
{
    protected $casts = [
        'is_billable' => 'boolean',
    ];
    protected $table = 'time_tracked_per_day_view';
}
