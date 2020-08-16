<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallSuggestor\Handlers;

use App\Algorithms\Calls\NextCallSuggestor\HandlerResponse;
use App\Algorithms\Calls\NextCallSuggestor\Suggestion;
use App\Contracts\CallHandler;

class SuccessfulCall implements CallHandler
{
    public function createSchedulerInfoString(Suggestion $prediction)
    {
        $status = '<span style="color: #008000">successfully</span>';

        $result = 'You just called '.$prediction->patient->getFullName()
                  .' '.$status.' in <b>week '
                  .now()->weekOfMonth.'. </b> <br/> <br/> <b>'
                  .'Please confirm or amend the above next predicted call time. </b>';

        if (isset($prediction->nurse) && $prediction->nurse !== auth()->id() && isset($prediction->nurse_display_name)) {
            $nurseName = $prediction->nurse_display_name;
            $result .= "<br/><br/>Note: Next call will be assigned to <b>$nurseName</b>";
        }

        return $result;
    }

    public function getNextCallDate(
        int $patientId,
        int $ccmTimeInSeconds,
        int $currentWeekOfMonth,
        int $successfulCallsThisMonth,
        int $patientPreferredNumberOfMonthlyCalls
    ): HandlerResponse {
        if ($ccmTimeInSeconds > 1199) { // More than 20 mins
            $once_monthly = 1 == $patientPreferredNumberOfMonthlyCalls;

            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) { // We are in the first two weeks of the month
                if ($once_monthly) {
                    //handle all cases with 28 days, prevent jump on 31st to next+1 mon, 'Add a month, 1x preference override'th
                    return new HandlerResponse(now()->addMonth()->startOfMonth(), 'Patient wants to be called 1x monthly and already has over 20 minutes CCM time.');
                }

                return new HandlerResponse(now()->endOfMonth()->subWeek(), 'Call patient in the last week of the month');
            }
            if (3 == $currentWeekOfMonth || 4 == $currentWeekOfMonth) { //second last week of month
                if ($once_monthly) {
                    return new HandlerResponse(now()->addWeeks(3), 'Add three weeks,1x preference override');
                }

                return new HandlerResponse(now()->addMonths(1)->startOfMonth()->addDays(3), 'First week of next month, [but at least 7 days in future]');
            }
            if (5 == $currentWeekOfMonth) {
                return new HandlerResponse(now()->addWeeks(2), 'Call patient after two weeks');
            }
        } elseif ($ccmTimeInSeconds > 899) { // 15 - 20 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) { // We are in the first two weeks of the month
                return new HandlerResponse(now()->addWeeks(2), 'Add two weeks');
            }
            if (3 == $currentWeekOfMonth) { //second last week of month
                return new HandlerResponse(now()->addWeek(), 'Add Week');
            }
            if (4 == $currentWeekOfMonth) { //second last week of month
                if ($ccmTimeInSeconds > 1020) {
                    return new HandlerResponse(now(), 'Greater than 17, same day, add attempt note', 'Please review careplan');
                }

                return new HandlerResponse(now()->addWeek(), 'Less than 17, add week. ');
            }
            if (5 == $currentWeekOfMonth) { //last few days of month
                if ($ccmTimeInSeconds > 1020) {
                    return new HandlerResponse(now(), 'Greater than 17 mins, same day, add attempt note', 'Please review careplan');
                }

                return new HandlerResponse(now()->addWeek(), 'Less than 17, add week. ');
            }
        } elseif ($ccmTimeInSeconds > 599) { // 10 - 15 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) { // We are in the first two weeks of the month
                return new HandlerResponse(now()->addWeeks(2), 'Call patient in 2 weeks.');
            }
            if (3 == $currentWeekOfMonth || 4 == $currentWeekOfMonth) { //second last week of month
                return new HandlerResponse(now()->addWeek(), 'Call patient after one week');
            }
            if (5 == $currentWeekOfMonth) { //last-ish week of month
                return new HandlerResponse(now()->addWeek(), 'Call patient after one week');
            }
        } else { // 0 - 10 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) { // We are in the first two weeks of the month
                return new HandlerResponse(now()->addWeek(), 'Call patient after a week');
            }
            if (3 == $currentWeekOfMonth || 4 == $currentWeekOfMonth) { //second last week of month
                return new HandlerResponse(now()->addWeek(), 'Call patient after a week');
            }
            if (5 == $currentWeekOfMonth) { //last-ish week of month
                return new HandlerResponse(now()->addWeek(), 'Call patient after a week');
            }
        }

        return new HandlerResponse(now(), 'Processed nothing. Returning current time');
    }
}
