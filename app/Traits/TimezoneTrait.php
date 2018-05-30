<?php

use Carbon\Carbon;

namespace App\Traits;

/**
 * App\Traits\TimezoneTrait
 * 
 * @property-read mixed $timezone_abbr
 * @property-read mixed $timezone_offset
 * @property-read mixed $timezone_offset_hours
 */
trait TimezoneTrait
{
    public function getTimezoneAbbrAttribute()
    {
        return $this->timezone
            ? \Carbon\Carbon::now($this->timezone)->format('T')
            : \Carbon\Carbon::now()->setTimezone()->format('T');
    }

    public function getTimezoneOffsetAttribute()
    {
        return $this->timezone
        ? \Carbon\Carbon::now($this->timezone)->offset
        : \Carbon\Carbon::now()->setTimezone('America/New_York')->offset;
    }

    public function getTimezoneOffsetHoursAttribute()
    {
        return $this->timezone
        ? \Carbon\Carbon::now($this->timezone)->offsetHours
        : \Carbon\Carbon::now()->setTimezone('America/New_York')->offsetHours;
    }

    public function resolveTimezone(\Carbon\Carbon $date) {
        return $date->timezone($this->timezone ?? 'America/New_York');
    }

    public function resolveTimezoneToGMT($date) {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        $date = $date->timezone($this->timezone ?? 'America/New_York');
        return $date->format('Y-m-d H:i:s') . ' GMT' . $this->timezone_offset_hours;
    }
}
