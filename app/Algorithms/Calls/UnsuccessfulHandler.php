<?php

namespace App\Algorithms\Calls;


use App\Call;
use App\Contracts\CallHandler;
use App\PatientInfo;
use Carbon\Carbon;

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

    private $week;
    private $patient;
    private $ccmTime;
    private $nurse;
    private $nurses = [];
    private $nextCallDate;
    private $attemptNote;
    private $matchArray = [];

    //return package
    private $prediction;

    //debug vars
    private $logic;

    public function __construct(
        PatientInfo $calledPatient,
        Carbon $initTime
    ) {

        $this->week = $initTime->weekOfMonth;
        $this->patient = $calledPatient;
        $this->ccmTime = $calledPatient->cur_month_activity_time;
        $this->nextCallDate = $initTime;
        $this->logic = '';
        $this->attemptNote = '';
        $this->prediction = [];

    }

    //exec
    public function handle()
    {

        //Calculate the next date before which we can call patient
        $this->getPatientOffset($this->ccmTime, $this->week);
        
        //get the next call date based on patient preferences
        $this->getNextWindow();

        //attach nurse to call, if any windows match.
        $this->findNurse();

        //Add debug string
        $this->prediction['predicament'] = $this->createSchedulerInfoString();

        return $this->prediction;

    }

    public function getPatientOffset(
        $ccmTime,
        $week
    ) {

        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient->user,
            Carbon::now()->toDateTimeString());

        if ($ccmTime > 1199) { // More than 20 mins

            if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                    $this->logic = 'End of month, minus one week';

                    return $this->nextCallDate->endOfMonth()->subWeek(1);

                } else {

                    $this->logic = 'Next window';

                    return $this->nextCallDate;

                }

            } else {
                if ($week == 3 || $week == 4) { //second last week of month

                    if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                        $this->logic = 'First week of next month';

                        return $this->nextCallDate->addMonth()->firstOfMonth();

                    } else {

                        $this->logic = 'Next window';

                        return $this->nextCallDate;

                    }

                } else {
                    if ($week == 5) { //last-ish week of month

                        if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                            $this->logic = 'First week of next month';

                            return $this->nextCallDate->addMonth()->firstOfMonth();

                        } else {

                            $this->logic = 'Next Day';

                            return $this->nextCallDate->addDay(1);

                        }

                    }
                }
            }


        } else {
            if ($ccmTime > 899) { // 15 - 20 mins

                if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                    if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                        $this->logic = 'Add one week';

                        return $this->nextCallDate->addWeek(1);

                    } else {

                        $this->logic = 'Next window';

                        return $this->nextCallDate;

                    }

                } else {
                    if ($week == 3) { //second last week of month

                        if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                            $this->logic = 'Add one week';

                            return $this->nextCallDate->addWeek(1);

                        }

                    } else {
                        if ($week == 4) {

                            $this->logic = 'Next window';

                            return $this->nextCallDate;


                        } else {
                            if ($week == 5) { //last-ish week of month
                                if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                                    if ($ccmTime > 1020) {

                                        $this->logic = 'Greater than 17, same day, add attempt note';
                                        $this->attemptNote = 'Please review careplan';

                                        return $this->nextCallDate;

                                    } else {

                                        $this->logic = 'Less than 17, tomorrow. ';

                                        return $this->nextCallDate->addDay(1);

                                    }

                                } else {

                                    $this->logic = 'Next Day';

                                    return $this->nextCallDate->addDay(1);

                                }

                            }
                        }
                    }
                }

            } else {
                if ($ccmTime > 599) { // 10 - 15 mins

                    if ($week == 1 || $week == 2 || $week == 3) { // We are in the first three weeks of the month

                        if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                            $this->logic = 'Add one week';

                            return $this->nextCallDate->addWeek(1);

                        } else {

                            $this->logic = 'Next window';

                            return $this->nextCallDate;

                        }

                    } else {
                        if ($week == 4) {

                            if ($successfulCallsThisMonth > 0) {

                                $this->logic = 'This Case Is Tricky, need to call this person on a Saturday or closest contact window';
                                $this->attemptNote = 'Call This Weekend';

                                return $this->nextCallDate->next(null);

                            } else {

                                $this->logic = 'Next window';

                                return $this->nextCallDate;


                            }

                        } else {

                            if ($week == 5) { //last-ish week of month

                                $this->logic = 'Less than 17, tomorrow. ';

                                return $this->nextCallDate->addDay(1);

                            }
                        }
                    }
                } else { // 0 - 10 mins

                    if ($week == 1 || $week == 2) {

                        $three_weeks_ago = Carbon::now()->subWeek(3)->toDateTimeString();
                        $last_successful_call = Call::whereStatus('reached')
                            ->where('outbound_cpm_id', $this->patient->id)
                            ->where('called_date', '>=', $three_weeks_ago)
                            ->count();

                        if ($successfulCallsThisMonth > 0 && $last_successful_call > 0) {

                            $this->logic = 'Check for successful calls in last 3 weeks, found, ';
                            $this->attemptNote = 'Next Window';

                            return $this->nextCallDate;

                        } else {
                            if ($successfulCallsThisMonth > 0) {

                                $this->logic = 'Next Week';

                                return $this->nextCallDate->addWeek(1);

                            } else {

                                $this->logic = 'Next Week';

                                return $this->nextCallDate->addWeek(1);

                            }
                        }
                    } else {
                        if ($week == 3) {

                            if ($successfulCallsThisMonth > 0) { //If there was a successful call this month...

                                $this->logic = 'This Case Is Tricky, need to call this person on a Saturday';
                                $this->attemptNote = 'Call This Weekend';

                                return $this->nextCallDate->next(Carbon::SATURDAY);

                            } else {

                                $this->logic = 'Next window';

                                return $this->nextCallDate->addDay(1);

                            }

                        } else {
                            if ($week == 4 || $week == 5) {

                                $this->logic = 'Next window';

                                return $this->nextCallDate->addDay(1);

                            }
                        }
                    }

                }
            }
        }

        //If nothing matches, just return the same date
        return $this->nextCallDate;

    }

    public function createSchedulerInfoString()
    {

        $status = '<span style="color: red">unsuccessfully</span>';

        return
            'You just called ' . $this->patient->user->fullName
            . ' ' . $status . ' in <b>week '
            . $this->week . '. </b> <br/> <br/> <b>'
            . 'Please confirm or amend the above next predicted call time. </b>';

    }

}