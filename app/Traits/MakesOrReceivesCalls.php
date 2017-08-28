<?php

namespace App\Traits;

use Carbon\Carbon;

trait MakesOrReceivesCalls
{
    public function countCompletedCallsFor(Carbon $date)
    {
        return $this->completedCallsFor($date)->count();
    }

    public function completedCallsFor(Carbon $date)
    {
        return $this->callsFor($date, ['reached', 'not reached']);
    }

    public function countCompletedCallsForToday()
    {
        return $this->completedCallsForToday()->count();
    }

    public function completedCallsForToday()
    {
        return $this->callsFor(Carbon::now(), ['reached', 'not reached']);
    }

    public function callsFor(Carbon $date, $type)
    {
        if (!is_array($type)) {
            $type = [$type];
        }

        return $this->calls()
            ->where('scheduled_date', '=', $date->toDateString())
            ->whereIn('calls.status', $type)
            ->get();
    }

    public function countScheduledCallsForToday()
    {
        return $this->scheduledCallsForToday()->count();
    }

    public function scheduledCallsForToday()
    {
        return $this->callsFor(Carbon::now(), 'scheduled');
    }

    public function countScheduledCallsFor(Carbon $date)
    {
        return $this->scheduledCallsFor($date)->count();
    }

    /**
     * Get the calls that were scheduled for a certain day, regardless of status.
     *
     * @param Carbon $date
     *
     * @return mixed
     */
    public function scheduledCallsFor(Carbon $date)
    {
        return $this->calls()
            ->where('scheduled_date', '=', $date->toDateString())
            ->get();
    }

    public function countSuccessfulCallsForToday()
    {
        return $this->successfulCallsForToday()->count();
    }

    /**
     * Calls that were scheduled for today and were actually made today
     *
     * @return mixed
     */
    public function successfulCallsForToday()
    {
        return $this->calls()
            ->where([
                ['scheduled_date', '=', Carbon::now()->toDateString()],
                ['called_date', '=', Carbon::now()->toDateString()],
                ['calls.status', '=','reached'],
            ])
            ->get();
    }
}