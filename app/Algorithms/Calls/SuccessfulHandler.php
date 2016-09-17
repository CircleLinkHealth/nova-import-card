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

//Readme
/*

    ------------------------------------*---------------------------------------
    Currently, the factors taken into consideration are:
      - Call Status (Reached, Unreached)
      - Current Month's CCM Time Bracket (0-10, 10-15, 15-20, >20)
      - Week Number in current Month (1, 2, 3, 4, 5) [Special Consideration for months with 5 weeks]
      - No Of Successful Calls to Patient
     ------------------------------------*---------------------------------------

WEEKS:

Week 1: 1-7
Week 2: 8-14
Week 3: 15-21
Week 4: 22-28
Week 5: 29-31 (not for Feb)

*/

class SuccessfulHandler implements CallHandler
{

    use CallAlgoHelper;

    private $week;
    private $patient;
    private $ccmTime;
    private $nextCallDate;
    private $attemptNote;
    
    //debug vars
    private $logic;


    public function __construct(PatientInfo $calledPatient)
    {

        $this->week = Carbon::now()->weekOfMonth;
        $this->patient = $calledPatient;
        $this->ccmTime = $calledPatient->cur_month_activity_time;
        $this->nextCallDate = Carbon::now();
        $this->logic = '';
        $this->attemptNote = '';

    }

    public function handle()
    {
        //Calculate the next date before which we can call patient
        $this->getPatientOffset();

        //get the next call date based on patient preferences
        $callDate = $this->getNextWindow();
        
        //Add debug string
        $callDate['predicament'] = $this->createSchedulerInfoString();

        return $callDate;

    }
    
    public function getPatientOffset(){

        if ($this->ccmTime > 1199) { // More than 20 mins

            //find out if patient likes to be called monthly
            $once_monthly = ($this->patient->preferred_calls_per_month == 1) ? true : false;

            if ($this->week == 1 || $this->week == 2) { // We are in the first two weeks of the month

                if ($once_monthly) {

                    $this->logic = 'Add a month, 1x preference override';
                    $this->nextCallDate->addMonth(1);

                } else {

                    $this->logic = 'Call patient in the last week of the month';
                    $this->nextCallDate->endOfMonth()->subWeek(1);

                }

            } else if ($this->week == 3 || $this->week == 4) { //second last week of month

                if ($once_monthly) {

                    $this->logic = 'Add three weeks,1x preference override';
                    $this->nextCallDate->addWeek(3);

                } else {

                    $this->logic = 'Call patient in the first week of the next month';
                    $this->nextCallDate->addMonth(1)->startOfMonth();

                }

            } else if ($this->week == 5) { //last-ish week of month

                $this->logic = 'Call patient after two weeks';
                $this->nextCallDate->addWeek(2);

            }

        }

        else if ($this->ccmTime > 899) { // 15 - 20 mins

            if ($this->week == 1 || $this->week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Add two weeks';
                $this->nextCallDate->addWeek(2);

            } else if ($this->week == 3) { //second last week of month

                $this->logic = 'Add Week';
                $this->nextCallDate->addWeek(1);

            } else if ($this->week == 4) { //second last week of month


                if ($this->ccmTime > 1020) {

                    $this->logic = 'Greater than 17, same day, add attempt note';
                    $this->attemptNote = 'Please review careplan';

                    $this->nextCallDate;

                } else {

                    $this->logic = 'Less than 17, add week. ';
                    $this->nextCallDate->addWeek(1);

                }


            } else if ($this->week == 5) { //last few days of month

                if ($this->ccmTime > 1020) {

                    $this->logic = 'Greater than 17 mins, same day, add attempt note';
                    $this->attemptNote = 'Please review careplan';

                    $this->nextCallDate;

                } else {

                    $this->logic = 'Less than 17, add week. ';
                    $this->nextCallDate->addWeek(1);

                }

            }

        }

        else if ($this->ccmTime > 599) { // 10 - 15 mins

            if ($this->week == 1 || $this->week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Call patient in 2 weeks.';
                $this->nextCallDate->addWeek(2);

            } else if ($this->week == 3 || $this->week == 4) { //second last week of month

                $this->logic = 'Call patient after one week';
                $this->nextCallDate->addWeek(1);

            } else if ($this->week == 5) { //last-ish week of month

                $this->logic = 'Call patient after one week';
                $this->nextCallDate->addWeek(1);

            }

        }

        else { // 0 - 10 mins

            if ($this->week == 1 || $this->week == 2) { // We are in the first two weeks of the month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            } else if ($this->week == 3 || $this->week == 4) { //second last week of month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            } else if ($this->week == 5) { //last-ish week of month

                $this->logic = 'Call patient after a week';
                $this->nextCallDate->addWeek(1);

            }

        }

        //If nothing matches, just return the same date
        $this->nextCallDate;

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