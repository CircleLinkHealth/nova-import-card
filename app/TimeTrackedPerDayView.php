<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeTrackedPerDayView extends Model
{
    protected $casts = [
        'is_billable' => 'boolean',
    ];
    protected $table = 'time_tracked_per_day_view';
}
