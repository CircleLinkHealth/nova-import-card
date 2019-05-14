<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use Illuminate\Support\Collection;

class PatientContactWindowUpdated
{

    const TWENTY_MINUTES = 1200;
    const WEEKDAYS_COUNT = 7;

    public function __construct()
    {
    }

    public function handle(PatientContactWindowUpdatedEvent $event)
    {
        // @var $auth \CircleLinkHealth\Customer\Entities\User
        $auth = auth()->user();

        if ( ! $auth) {
            return;
        }

        if ( ! $auth->isCareCoach()) {
            return;
        }

        $windowsCollection = collect($event->windows);
        $window     = $windowsCollection->first();

        $patientId = $window->patient_info->user_id;

        $calls = Call::where('inbound_cpm_id', '=', $patientId)
                     ->where('status', '=', 'scheduled')
                     ->whereIn('scheduler', ['core algorithm', 'rescheduler algorithm'])
                     ->get();

        if ($calls->isEmpty()) {
            return;
        }

        $contactDays = $windowsCollection
            ->unique('day_of_week')
            ->pluck('day_of_week')
            ->map(function ($elem) {
                return (int)$elem;
            });

        //NOTE:
        //not recommended way to update sql entries
        //each loop does a trip to the database,
        //which is a sign of bad practice (bad for performance)
        //however, for this use case, we know that only a small number of calls
        //will be updated (usually 1)
        $calls->each(
            function (Call $c) use ($window, $contactDays) {

                $scheduledDate = Carbon::parse($c->scheduled_date);
                if ( ! $contactDays->contains(carbonToClhDayOfWeek($scheduledDate->dayOfWeek))) {
                    $newDate = $this->getNextAvailableContactDate($scheduledDate, $contactDays, $window);
                    if ( ! $newDate->equalTo($scheduledDate)) {
                        $c->scheduled_date = $newDate;
                    }
                }

                if ($c->window_start != $window->window_time_start) {
                    $c->window_start = $window->window_time_start;
                }

                if ($c->window_end != $window->window_time_end) {
                    $c->window_end = $window->window_time_end;
                }

                if ($c->isDirty()) {
                    $c->save();
                }
            }
        );
    }

    /**
     *
     * Get the immediate next available date from a list from week days.
     * if the next date moves into the next month, do not move if:
     *  - patient has less than 20 minutes
     *  - patient has 0 successful calls
     *
     * @param Carbon $currentDate
     * @param Collection $availableDays
     * @param PatientContactWindow $window
     *
     * @return Carbon
     */
    private function getNextAvailableContactDate(Carbon $currentDate, Collection $availableDays, PatientContactWindow $window)
    {
        if ($availableDays->isEmpty()) {
            return $currentDate;
        }

        $currentDay = carbonToClhDayOfWeek($currentDate->dayOfWeek);

        //try going to the next available day
        $candidateDay = -1;
        foreach ($availableDays as $day) {
            if ($day > $currentDay) {
                $candidateDay = $day;
                break;
            }
        }

        if (-1 == $candidateDay) {
            // the next available day may be next week
            // current day 5, candidate 2. next week: 7 - (5 - 2)
            $candidateDay = $availableDays->first();
            $diff         = PatientContactWindowUpdated::WEEKDAYS_COUNT - ($currentDay - $candidateDay);
        } else {
            $diff = $candidateDay - $currentDay;
        }

        $temp = $currentDate->copy()->addDays($diff);

        //check if new date is in next month
        if ($temp->month != $currentDate->month) {

            $patient = $window->patient_info->user;

            /**
             * @var PatientMonthlySummary $summary
             */
            $summary = $patient->patientSummaryForMonth($currentDate);

            //do not move the date to next month if patient has less than 20 minutes ccm time or has no successful calls
            if ($summary->ccm_time < PatientContactWindowUpdated::TWENTY_MINUTES || $summary->no_of_successful_calls === 0) {
                return $currentDate;
            }

        }

        return $temp;
    }
}
