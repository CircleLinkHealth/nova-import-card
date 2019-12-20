<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Call;
use App\Contracts\CallHandler;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;

//READ ME
/*
 * Signature: 'core algorithm'
 *
 * ------------------------------------*---------------------------------------
 * Currently, the factors taken into consideration are:
 * - Call Status (Reached, Unreached)
 * - Current Month's CCM Time Bracket (0-10, 10-15, 15-20, >20)
 * - Week Number in current Month (1, 2, 3, 4, 5) [Special Consideration for months with 5 weeks]
 * - No Of Successful Calls to Patient
 * ------------------------------------*---------------------------------------
 * WEEKS:
 * Week 1: 1-7
 * Week 2: 8-14
 * Week 3: 15-21
 * Week 4: 22-28
 * Week 5: 29-31 (not for Feb)
 *
 * returns an array of the new call date, start window, and end window.
 *
*/

class UnsuccessfulHandler implements CallHandler
{
    use CallAlgoHelper;
    private $attemptNote;
    private $ccmTime;

    //debug vars
    private $logic;
    private $matchArray = [];
    private $nextCallDate;
    private $nurse;
    private $nurses = [];
    private $patient;

    //return package
    private $prediction;
    private $prevCall;

    private $week;

    public function __construct(
        Patient $calledPatient,
        Carbon $initTime,
        $previousCall = null
    ) {
        $this->week         = $initTime->weekOfMonth;
        $this->patient      = $calledPatient;
        $this->ccmTime      = $calledPatient->user->getCcmTime();
        $this->nextCallDate = $initTime;
        $this->logic        = '';
        $this->attemptNote  = '';
        $this->prediction   = [];
        $this->prevCall     = $previousCall;
    }

    public function createSchedulerInfoString()
    {
        $status = '<span style="color: red">unsuccessfully</span>';
        $result = 'You just called '.$this->patient->user->getFullName()
                  .' '.$status.' in <b>week '
                  .$this->week.'. </b> <br/> <br/> <b>'
                  .'Please confirm or amend the above next predicted call time. </b>';

        if ($this->prediction['nurse'] !== auth()->id() && isset($this->prediction['nurse_display_name'])) {
            $nurseName = $this->prediction['nurse_display_name'];
            $result .= "<br/><br/>Note: Next call will be assigned to <b>$nurseName</b>";
        }

        return $result;
    }

    public function getPatientOffset(
        $ccmTime,
        $week
    ) {
        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth(
            $this->patient->user,
            Carbon::now()->toDateTimeString()
        );

        if ($ccmTime > 1199) {
            if ($successfulCallsThisMonth > 0) {
                $this->logic = 'First week of next month.';

                return $this->nextCallDate->addMonth()->startOfMonth();
            }

            if (1 == $week) {
                $this->logic = 'Next week';

                return $this->nextCallDate->addWeek()->startOfWeek();
            }

            $this->logic = 'Next Day';

            return $this->nextCallDate->addDay();
        }
        if ($ccmTime > 899) { // 15 - 20 mins
            if (1 == $week || 2 == $week) { // We are in the first two weeks of the month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    $this->logic = 'Add one week';

                    return $this->nextCallDate->addWeek();
                }
                $this->logic = 'Next Day';

                return $this->nextCallDate->addDay();
            }
            if (3 == $week) { //second last week of month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    $this->logic = 'Add one week';

                    return $this->nextCallDate->addWeek();
                }
            } elseif (4 == $week) {
                $this->logic = 'Next Day';

                return $this->nextCallDate->addDay();
            } elseif (5 == $week) { //last-ish week of month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    if ($ccmTime > 1020) {
                        $this->logic       = 'Greater than 17, same day, add attempt note';
                        $this->attemptNote = 'Please review careplan';

                        return $this->nextCallDate;
                    }
                    $this->logic = 'Less than 17, tomorrow. ';

                    return $this->nextCallDate->addDay(1);
                }
                $this->logic = 'Next Day';

                return $this->nextCallDate->addDay(1);
            }
        } elseif ($ccmTime > 599) { // 10 - 15 mins
            if (1 == $week || 2 == $week || 3 == $week) { // We are in the first three weeks of the month
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    $this->logic = 'Add one week';

                    return $this->nextCallDate->addWeek(1);
                }
                $this->logic = 'Next window';

                return $this->nextCallDate;
            }
            if (4 == $week) {
                if ($successfulCallsThisMonth > 0) {
                    $this->logic       = 'This Case Is Tricky, need to call this person on a Saturday or closest contact window';
                    $this->attemptNote = 'Call This Weekend';

                    return $this->nextCallDate->next(null);
                }
                $this->logic = 'Next window';

                return $this->nextCallDate;
            }
            if (5 == $week) { //last-ish week of month
                $this->logic = 'Less than 17, tomorrow. ';

                return $this->nextCallDate->addDay(1);
            }
        } else { // 0 - 10 mins
            if (1 == $week || 2 == $week) {
                $three_weeks_ago      = Carbon::now()->subWeek(3)->toDateTimeString();
                $last_successful_call = Call::whereStatus('reached')
                    ->where('outbound_cpm_id', $this->patient->id)
                    ->where('called_date', '>=', $three_weeks_ago)
                    ->count();

                if ($successfulCallsThisMonth > 0 && $last_successful_call > 0) {
                    $this->logic       = 'Check for successful calls in last 3 weeks, found, ';
                    $this->attemptNote = 'Next Window';

                    return $this->nextCallDate;
                }
                if ($successfulCallsThisMonth > 0) {
                    $this->logic = 'Next Week';

                    return $this->nextCallDate->addWeek(1);
                }
                $this->logic = 'Next Week';

                return $this->nextCallDate->addWeek(1);
            }
            if (3 == $week) {
                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...
                    $this->logic       = 'This Case Is Tricky, need to call this person on a Saturday';
                    $this->attemptNote = 'Call This Weekend';

                    return $this->nextCallDate->next(Carbon::SATURDAY);
                }
                $this->logic = 'Next window';

                return $this->nextCallDate->addDay(1);
            }
            if (4 == $week || 5 == $week) {
                $this->logic = 'Next window';

                return $this->nextCallDate->addDay(1);
            }
        }

        //If nothing matches, just return the same date
        return $this->nextCallDate;
    }

    //exec
    public function handle()
    {
        //Calculate the next date before which we can call patient
        $this->getPatientOffset($this->ccmTime, $this->week);

        //get the next call date based on patient preferences
        $this->getNextWindow();

        $result = (new NurseFinder(
            $this->patient,
            $this->nextCallDate,
            $this->prediction['window_start'],
            $this->prediction['window_end'],
            $this->prevCall
        ))
            ->find();

        $this->prediction = (collect($this->prediction))->merge($result)->toArray();

        $this->prediction['patient'] = $this->patient;

        //Add debug string
        $this->prediction['predicament'] = $this->createSchedulerInfoString();

        return $this->prediction;
    }
}
