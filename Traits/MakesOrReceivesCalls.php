<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trait MakesOrReceivesCalls.
 */
trait MakesOrReceivesCalls
{
    public function completedCallsFor(Carbon $date)
    {
        return $this->calls()
            ->where([
                ['called_date', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
            ])->whereIn('calls.status', ['reached', 'not reached']);
    }

    /**
     * Returns today's completed calls.
     * Completed Call: A call that was placed today and was either successful, or unsuccessful. It doesn’t matter when
     * it was scheduled for.
     *
     * @return Collection
     */
    public function completedCallsForToday()
    {
        return $this->completedCallsFor(Carbon::now())->get();
    }

    public function countCompletedCallsFor(Carbon $date)
    {
        return $this->completedCallsFor($date)->count();
    }

    public function countCompletedCallsForToday()
    {
        return $this->completedCallsFor(Carbon::now())->count();
    }

    /**
     * Returns a count of the calls that were scheduled on a given day.
     *
     * @return int
     */
    public function countScheduledCallsFor(Carbon $date)
    {
        return $this->scheduledCallsFor($date)->count();
    }

    public function countScheduledCallsForToday()
    {
        return $this->scheduledCallsFor(Carbon::now())->count();
    }

    /**
     * Returns a count of the successful calls on a given day.
     *
     * @return int
     */
    public function countSuccessfulCallsFor(Carbon $date)
    {
        return $this->successfulCallsFor($date)->count();
    }

    /**
     * Returns a count of the successful calls made today.
     *
     * @return int
     */
    public function countSuccessfulCallsMadeToday()
    {
        return $this->successfulCallsFor(Carbon::now())->count();
    }

    /**
     * Returns a count of the unsuccessful calls on a given day.
     *
     * @return int
     */
    public function countUnSuccessfulCallsFor(Carbon $date)
    {
        return $this->unSuccessfulCallsFor($date)->count();
    }

    /**
     * Get the calls that were scheduled for a certain day, regardless of status.
     * In other words, a call may have ben scheduled for a certain date, but it actually happened earlier.
     *
     * @return mixed
     */
    public function scheduledCallsFor(Carbon $date)
    {
        return $this->calls()
            ->where(function ($q) use ($date) {
                $q->where([
                    ['scheduled_date', '=', $date->toDateString()],
                    ['called_date', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                    ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
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
            });
    }

    /**
     * Returns today's scheduled calls.
     * Scheduled Call: A call that was scheduled for today and either was placed today, or not placed yet.
     *
     * @return Collection
     */
    public function scheduledCallsForToday()
    {
        return $this->scheduledCallsFor(Carbon::now())
            ->get();
    }

    /**
     * @return mixed
     */
    public function successfulCallsFor(Carbon $date)
    {
        return $this->calls()
            ->where([
                ['called_date', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                ['calls.status', '=', 'reached'],
            ]);
    }

    /**
     * Returns today's successful calls.
     * Successful Call: A call that was placed today and was successful. It does not matter if the call was scheduled
     * for tomorrow.
     *
     * @return Collection
     */
    public function successfulCallsMadeToday()
    {
        return $this->successfulCallsFor(Carbon::now())->get();
    }

    public function unsuccessfulCallsFor(Carbon $date)
    {
        return $this->calls()
            ->where([
                ['called_date', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                ['calls.status', '=', 'not reached'],
            ]);
    }
}
