<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallCalculator\Handlers;

use App\Algorithms\Calls\NextCallCalculator\CallHandlerResponse;
use App\Algorithms\Calls\NextCallCalculator\NextCallPrediction;
use App\Call;
use App\Contracts\CallHandler;
use Carbon\Carbon;

class UnsuccessfulHandler implements CallHandler
{
    public function createSchedulerInfoString(NextCallPrediction $prediction)
    {
        $status = '<span style="color: red">unsuccessfully</span>';
        $result = 'You just called '.$prediction->patient->getFullName()
                  .' '.$status.' in <b>week '
                  .now()->weekOfMonth.'. </b> <br/> <br/> <b>'
                  .'Please confirm or amend the above next predicted call time. </b>';

        if ($prediction->nurse !== auth()->id() && isset($prediction->nurse_display_name)) {
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
    ): CallHandlerResponse {
        if ($ccmTimeInSeconds > 1199) {
            if ($successfulCallsThisMonth > 0) {
                return new CallHandlerResponse(now()->addMonth()->startOfMonth(), 'First week of next month.');
            }

            if (1 == $currentWeekOfMonth) {
                return new CallHandlerResponse(now()->addWeek()->startOfWeek(), 'Next week');
            }

            return new CallHandlerResponse(now()->addDay(), 'Next Day');
        }
        if ($ccmTimeInSeconds > 899) { // 15 - 20 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) { // We are in the first two weeks of the month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    return new CallHandlerResponse(now()->addWeek(), 'Add one week');
                }

                return new CallHandlerResponse(now()->addDay(), 'Next Day');
            }
            if (3 == $currentWeekOfMonth) { //second last week of month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    return new CallHandlerResponse(now()->addWeek(), 'Add one week');
                }
            } elseif (4 == $currentWeekOfMonth) {
                return new CallHandlerResponse(now()->addDay(), 'Next Day');
            } elseif (5 == $currentWeekOfMonth) { //last-ish week of month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    if ($ccmTimeInSeconds > 1020) {
                        return new CallHandlerResponse(now(), 'Greater than 17, same day, add attempt note', 'Please review careplan');
                    }

                    return new CallHandlerResponse(now()->addDay(), 'Less than 17, tomorrow. ');
                }

                return new CallHandlerResponse(now()->addDay(), 'Next Day');
            }
        } elseif ($ccmTimeInSeconds > 599) { // 10 - 15 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth || 3 == $currentWeekOfMonth) { // We are in the first three weeks of the month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    return new CallHandlerResponse(now()->addWeek(), 'Add one week');
                }

                return new CallHandlerResponse(now(), 'Next window');
            }
            if (4 == $currentWeekOfMonth) {
                if ($successfulCallsThisMonth > 0) {
                    $this->attemptNote = 'Call This Weekend';

                    return new CallHandlerResponse(now()->next(null), 'This Case Is Tricky, need to call this person on a Saturday or closest contact window');
                }

                return new CallHandlerResponse(now(), 'Next window');
            }
            if (5 == $currentWeekOfMonth) { //last-ish week of month
                return new CallHandlerResponse(now()->addDay(), 'Less than 17, tomorrow. ');
            }
        } else { // 0 - 10 mins
            if (1 == $currentWeekOfMonth || 2 == $currentWeekOfMonth) {
                $three_weeks_ago      = Carbon::now()->subWeek(3)->toDateTimeString();
                $last_successful_call = Call::whereStatus('reached')
                    ->where('outbound_cpm_id', $patientId)
                    ->where('called_date', '>=', $three_weeks_ago)
                    ->count();

                if ($successfulCallsThisMonth > 0 && $last_successful_call > 0) {
                    return new CallHandlerResponse(now(), 'Check for successful calls in last 3 weeks, found, ', 'Next Window');
                }
                if ($successfulCallsThisMonth > 0) {
                    return new CallHandlerResponse(now()->addWeek(), 'Next Week');
                }

                return new CallHandlerResponse(now()->addWeek(), 'Next Week');
            }
            if (3 == $currentWeekOfMonth) {
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    $this->attemptNote = 'Call This Weekend';

                    return new CallHandlerResponse(now()->next(Carbon::SATURDAY), 'This Case Is Tricky, need to call this person on a Saturday');
                }

                return new CallHandlerResponse(now()->addDay(), 'Next window');
            }
            if (4 == $currentWeekOfMonth || 5 == $currentWeekOfMonth) {
                return new CallHandlerResponse(now()->addDay(), 'Next window');
            }
        }

        return new CallHandlerResponse(now(), 'Processed nothing. Returning current time');
    }
}
