<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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

    public function callsFor(Carbon $scheduledDate, Carbon $calledDate = null, $status = null)
    {
        if (!is_array($status) && !empty($status)) {
            $status = [$status];
        }

        $args = [
            ['scheduled_date', '=', $scheduledDate->toDateString()],
        ];

        $q = $this->calls()
            ->where($args);

        if ($calledDate) {
            $args[] = ['called_date', '>=', $calledDate->startOfDay()->toDateTimeString()];
            $args[] = ['called_date', '<=', $calledDate->endOfDay()->toDateTimeString()];
        }

        if ($status) {
            $q->whereIn('calls.status', $status);
        }

        return $q->get();
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
        $calls = $this->calls()
            ->where([
                ['called_date', '>=', Carbon::now()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', Carbon::now()->endOfDay()->toDateTimeString()],
            ])->whereIn('calls.status', ['reached', 'not reached'])
            ->get();

        return $calls;
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
        $calls = $this->calls()
            ->where(function ($q) {
                $q->where([
                    ['scheduled_date', '=', Carbon::now()->toDateString()],
                    ['called_date', '>=', Carbon::now()->startOfDay()->toDateTimeString()],
                    ['called_date', '<=', Carbon::now()->endOfDay()->toDateTimeString()],
                ])
                    ->orWhere([
                        ['scheduled_date', '=', Carbon::now()->toDateString()],
                        ['called_date', '=', null],
                        ['calls.status', '=', 'scheduled'],
                    ]);
            })
            ->get();

        return $calls;
    }

    /**
     * Returns a count of the calls that were scheduled on a given day.
     *
     * @param Carbon $date
     *
     * @return int
     */
    public function countCallsOriginallyScheduledFor(Carbon $date)
    {
        return $this->callsOriginallyScheduledFor($date)->count();
    }

    /**
     * Get the calls that were scheduled for a certain day, regardless of status.
     * In other words, a call may have ben scheduled for a certain date, but it actually happened earlier.
     *
     * @param Carbon $date
     *
     * @return Collection
     */
    public function callsOriginallyScheduledFor(Carbon $date)
    {
        return $this->callsFor($date);
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
        $calls = $this->calls()
            ->where([
                ['called_date', '>=', Carbon::now()->startOfDay()->toDateTimeString()],
                ['called_date', '<=', Carbon::now()->endOfDay()->toDateTimeString()],
                ['calls.status', '=', 'reached'],
            ])
            ->get();

        return $calls;
    }
}