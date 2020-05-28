<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use Carbon\Carbon;

/**
 * App\Traits\TimezoneTrait.
 *
 * @property mixed $timezone_abbr
 * @property mixed $timezone_offset
 * @property mixed $timezone_offset_hours
 */
trait TimezoneTrait
{
    public function getTimezoneAbbrAttribute()
    {
        return $this->timezone
            ? Carbon::now($this->timezone)->format('T')
            : Carbon::now()->setTimezone('America/New_York')->format('T');
    }

    public function getTimezoneOffsetAttribute()
    {
        return $this->timezone
            ? Carbon::now($this->timezone)->offset
            : Carbon::now()->setTimezone('America/New_York')->offset;
    }

    public function getTimezoneOffsetHoursAttribute()
    {
        return $this->timezone
            ? Carbon::now($this->timezone)->offsetHours
            : Carbon::now()->setTimezone('America/New_York')->offsetHours;
    }

    public function resolveTimezone(Carbon $date)
    {
        return $date->timezone($this->timezone ?? 'America/New_York');
    }

    public function resolveTimezoneToGMT($date)
    {
        if ( ! is_null($date)) {
            if (is_string($date)) {
                $date = Carbon::parse($date);
            }
            $date = $date->timezone('America/New_York');

            return $date->format('Y-m-d H:i:s');
        }
    }
}
