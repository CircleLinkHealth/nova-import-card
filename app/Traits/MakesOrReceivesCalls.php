<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait MakesOrReceivesCalls
 * @package App\Traits
 */

trait MakesOrReceivesCalls
{
    public function countCompletedCallsFor(Carbon $date)
    {
        return $this->completedCallsFor($date)->count();
    }

    public function completedCallsFor(Carbon $date)
    {
        $calls = $this->calls()
            ->where([
                ['called_date', '>=', $date->startOfDay()->toDateTimeString()],
                ['called_date', '<=', $date->endOfDay()->toDateTimeString()],
            ])->whereIn('calls.status', ['reached', 'not reached'])
            ->get();

        return $calls;
    }

    public function countCompletedCallsForToday()
    {
        return $this->completedCallsForToday()->count();
    }

    /**
     * Returns today's completed calls.
     * Completed Call: A call that was placed today and was either successful, or unsuccessful. It doesn’t matter when it was scheduled for.
     *
     * @return Collection
     */
    public function completedCallsForToday()
    {
        return $this->completedCallsFor(Carbon::now());
    }

    public function countScheduledCallsForToday()
    {
        return $this->scheduledCallsForToday()->count();
    }

    /**
     * Returns today's scheduled calls.
     * Scheduled Call: A call that was scheduled for today and either was placed today, or not placed yet.
     *
     * @return Collection
     */
    public function scheduledCallsForToday()
    {
        return $this->scheduledCallsFor(Carbon::now());
    }

    /**
     * Returns a count of the calls that were scheduled on a given day.
     *
     * @param Carbon $date
     *
     * @return int
     */
    public function countScheduledCallsFor(Carbon $date)
    {
        return $this->scheduledCallsFor($date)->count();
    }

    /**
     * Get the calls that were scheduled for a certain day, regardless of status.
     * In other words, a call may have ben scheduled for a certain date, but it actually happened earlier.
     *
     * @param Carbon $date
     *
     * @return Collection
     */
    public function scheduledCallsFor(Carbon $date)
    {
        $calls = $this->calls()
            ->where(function ($q) use ($date) {
                $q->where([
                    ['scheduled_date', '=', $date->toDateString()],
                    ['called_date', '>=', $date->startOfDay()->toDateTimeString()],
                    ['called_date', '<=', $date->endOfDay()->toDateTimeString()],
                ])
                    ->orWhere([
                        ['scheduled_date', '=', $date->toDateString()],
                        ['called_date', '=', null],
                        ['calls.status', '=', 'scheduled'],
                    ])
                    ->orWhere([
                        ['scheduled_date', '=', $date->toDateString()],
                        ['called_date', '=', null],
                        ['calls.status', '=', 'dropped'],
                    ]);
            })
            ->get();

        return $calls;
    }

    /**
     * Returns a count of the successful calls made today
     *
     * @return int
     */
    public function countSuccessfulCallsMadeToday()
    {
        return $this->successfulCallsMadeToday()->count();
    }

    /**
     * Returns today's successful calls.
     * Successful Call: A call that was placed today and was successful. It does not matter if the call was scheduled for tomorrow.
     *
     * @return Collection
     */
    public function successfulCallsMadeToday()
    {
        return $this->successfulCallsFor(Carbon::now());
    }

    public function successfulCallsfor(Carbon $date)
    {
        $calls = $this->calls()
            ->where([
                ['called_date', '>=', $date->startOfDay()->toDateTimeString()],
                ['called_date', '<=', $date->endOfDay()->toDateTimeString()],
                ['calls.status', '=', 'reached'],
            ])
            ->get();

        return $calls;
    }
}
