<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 12:08 PM
 */

namespace App\Algorithms\Calls;

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

class SuccessfulHandler implements CallHandler
{

    use CallAlgoHelper;
    
    const VERSION = '2.0';

    private $week;
    private $patient;
    private $nurse;
    private $nurses = [];
    private $ccmTime;
    private $nextCallDate;
    private $attemptNote;
    private $matchArray = [];

    //return package
    private $prediction;

    //debug vars
    private $logic;


    public function __construct(PatientInfo $calledPatient, Carbon $initTime)
    {

        $this->week = $initTime->weekOfMonth;
        $this->patient = $calledPatient;
        $this->ccmTime = $calledPatient->cur_month_activity_time;
        $this->nextCallDate = $initTime;
        $this->logic = '';
        $this->attemptNote = '';
        $this->prediction = [];

    }

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
    
    public function getPatientOffset($ccmTime, $week){

        if ($ccmTime > 1199) { // More than 20 mins

            //find out if patient likes to be called monthly
            $once_monthly = ($this->patient->preferred_calls_per_month == 1) ? true : false;

            if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                if ($once_monthly) {

                    $this->logic = 'Add a month, 1x preference override';
                    //handle all cases with 28 days, prevent jump on 31st to next+1 month
                    $this->nextCallDate->day(28);

                } else {

                    $this->logic = 'Call patient in the last week of the month';
                    $this->nextCallDate->endOfMonth()->subWeek(1);

                }

            } else if ($week == 3 || $week == 4) { //second last week of month

                if ($once_monthly) {

                    $this->logic = 'Add three weeks,1x preference override';
                    $this->nextCallDate->addWeek(3);

                } else {

                    $this->logic = 'Call patient in the first week of the next month';
                    $this->nextCallDate->addMonth(1)->startOfMonth();

                }

            } else if ($week == 5) { //last-ish week of month

                $this->logic = 'Call patient after two weeks';
                $this->nextCallDate->addWeek(2);

            }

        }

        else if ($ccmTime > 899) { // 15 - 20 mins

            if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Add two weeks';
                $this->nextCallDate->addWeek(2);

            } else if ($week == 3) { //second last week of month

                $this->logic = 'Add Week';
                $this->nextCallDate->addWeek(1);

            } else if ($week == 4) { //second last week of month


                if ($ccmTime > 1020) {

                    $this->logic = 'Greater than 17, same day, add attempt note';
                    $this->attemptNote = 'Please review careplan';

                    $this->nextCallDate;

                } else {

                    $this->logic = 'Less than 17, add week. ';
                    $this->nextCallDate->addWeek(1);

                }


            } else if ($week == 5) { //last few days of month

                if ($ccmTime > 1020) {

                    $this->logic = 'Greater than 17 mins, same day, add attempt note';
                    $this->attemptNote = 'Please review careplan';

                    $this->nextCallDate;

                } else {

                    $this->logic = 'Less than 17, add week. ';
                    $this->nextCallDate->addWeek(1);

                }

            }

        }

        else if ($ccmTime > 599) { // 10 - 15 mins

            if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Call patient in 2 weeks.';
                $this->nextCallDate->addWeek(2);

            } else if ($week == 3 || $week == 4) { //second last week of month

                $this->logic = 'Call patient after one week';
                $this->nextCallDate->addWeek(1);

            } else if ($week == 5) { //last-ish week of month

                $this->logic = 'Call patient after one week';
                $this->nextCallDate->addWeek(1);

            }

        }

        else { // 0 - 10 mins

            if ($week == 1 || $week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            } else if ($week == 3 || $week == 4) { //second last week of month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            } else if ($week == 5) { //last-ish week of month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            }

        }

        return $this->nextCallDate;

    }

    public function createSchedulerInfoString()
    {
        
        $status = '<span style="color: green">successfully</span>';

        return
            'You just called ' . $this->patient->user->fullName
            . ' ' . $status . ' in <b>week '
            . $this->week . '. </b> <br/> <br/> <b>'
            . 'Please confirm or amend the above next predicted call time. </b>';

    }
    
}