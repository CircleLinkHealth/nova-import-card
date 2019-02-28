<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Filters\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Carbon\Carbon $call_time_start
 * @property \Carbon\Carbon $call_time_end
 * @property \Carbon\Carbon $patient_created_at
 * @property string preferred_call_days
 * @property int|null no_call_attempts_since_last_success
 */
class CallView extends Model
{
    use Filterable;

    protected $table = 'calls_view';

    public function preferredCallDaysToExpandedString()
    {
        $windows = [];
        if ($this->preferred_call_days) {
            $days    = explode(',', $this->preferred_call_days);
            $start = Carbon::parse($this->call_time_start)->format('h:i a');
            $end   = Carbon::parse($this->call_time_end)->format('h:i a');

            foreach ($days as $day) {

                switch ($day) {
                    case 1:
                        $windows[] = "Monday: {$start} - {$end}<br/>";
                        break;
                    case 2:
                        $windows[] = "Tuesday: {$start} - {$end}<br/>";
                        break;
                    case 3:
                        $windows[] = "Wednesday: {$start} - {$end}<br/>";
                        break;
                    case 4:
                        $windows[] = "Thursday: {$start} - {$end}<br/>";
                        break;
                    case 5:
                        $windows[] = "Friday: {$start} - {$end}<br/>";
                        break;
                    case 6:
                        $windows[] = "Saturday: {$start} - {$end}<br/>";
                        break;
                    case 7:
                        $windows[] = "Sunday: {$start} - {$end}<br/>";
                        break;
                }
            }
        }

        return empty($windows)
            ? 'Patient call date/time preferences not found.'
            : implode($windows);
    }

    public function preferredCallDaysToString()
    {
        $days   = explode(',', $this->preferred_call_days);
        $result = [];
        foreach ($days as $day) {
            $result[] = $this->getDayFromInt($day);
        }

        return implode(',', $result);
    }

    private function getDayFromInt($dayInt)
    {
        switch ($dayInt) {
            case 1:
                return 'M';
            case 2:
                return 'Tu';
            case 3:
                return 'W';
            case 4:
                return 'Th';
            case 5:
                return 'F';
            case 6:
                return 'Sa';
            case 7:
                return 'Su';
            default:
                return '?';
        }
    }
}
