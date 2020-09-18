<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\SharedModels\Entities\Call;
use Carbon\Carbon;

/**
 * Trait MakesOrReceivesCalls.
 */
trait MakesOrReceivesCalls
{
    public function callsWithCallbacks(Carbon $date)
    {
        return Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', 'call')
                ->orWhere('sub_type', '=', 'Call Back');
        })
            ->where(function ($q) use ($date) {
                $q->where('outbound_cpm_id', $this->id)
                    ->orWhere('inbound_cpm_id', $this->id);
            })
            ->where('called_date', '>=', $date->startOfDay()->toDateTimeString())
            ->where('called_date', '<=', $date->endOfDay()->toDateTimeString());
    }

    public function completedCallsFor(Carbon $date)
    {
        return $this->callsWithCallbacks($date)->whereIn('calls.status', ['reached', 'not reached']);
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
     * Returns a count of the unsuccessful calls on a given day.
     *
     * @return int
     */
    public function countUnSuccessfulCallsFor(Carbon $date)
    {
        return $this->unsuccessfulCallsFor($date)->count();
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
     * This method does not collect calls marked as tyoe => task - i.e a callback.
     *
     * @return mixed
     */
    public function successfulCallsFor(Carbon $date)
    {
        return $this->callsWithCallbacks($date)->where('status', 'reached');
    }

    public function unsuccessfulCallsFor(Carbon $date)
    {
        return $this->callsWithCallbacks($date)
            ->where('calls.status', '=', 'not reached');
    }
}
