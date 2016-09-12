<?php namespace App\Algorithms\Calls;

use App\Call;
use App\PatientContactWindow;
use App\PatientInfo;
use App\Services\Calls\SchedulerService;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PredictCall
{

    /*
    ------------------------------------*---------------------------------------
    Currently, the factors taken into consideration are:
      - Call Status (Reached, Unreached)
      - Current Month's CCM Time Bracket (0-10, 10-15, 15-20, >20)
      - Week Number in current Month (1, 2, 3, 4) [Special Consideration for months with 5/6 weeks: (1, 2, 3+4, 5)]
      - No Of Successful Calls to Patient
     ------------------------------------*---------------------------------------
    */

    private $call;
    private $callStatus;
    private $patient;
    //Since you can't pass a note in for the reconcile function
    private $note = null;
    private $ccmTime;

    public function __construct(User $calledPatient, $currentCall, $currentCallStatus)
    {

        $this->call = $currentCall;
        $this->callStatus = $currentCallStatus;

        /*
        It is to be noted that this class should be patient independent,
        but certain components are not updated since scheduled
        calls are updated elsewhere. @todo search solution.
       */

        $this->patient = $calledPatient;

        $this->ccmTime = $this->patient->patientInfo->cur_month_activity_time;

    }

    /*
    In order to predict a call, a patient's factors are taken into consideration.
    These factors are the different switches that determine the best next
    plausible call date for a patient. Successful calls are handled
    differently from unsuccessful calls.
    */

    public function predict($note){

    $this->note = $note;


        if ($this->callStatus == true) {

            return $this->successfulCallHandler();

        } else {

            return $this->unsuccessfulCallHandler();

        }

    }

    public function successfulCallHandler(){

        //Update and close previous call
        if($this->call) {

            $this->call->status = 'reached';
            $this->call->note_id = $this->note->id;
            $this->call->called_date = Carbon::now()->toDateTimeString();
            $this->call->outbound_cpm_id = Auth::user()->ID;
            $this->call->save();

        } else { // If call doesn't exist, make one and store it

            (new NoteService)->storeCallForNote($this->note, 'reached', $this->patient, Auth::user(), 'outbound', 'algorithm');

        }

        //Here, we try to get the earliest contact window for the current patient,
        //and then determine the number of weeks to offset the next call by with
        //some analysis of the patient's current factors.

        //FACTORS

        //Set current day
        $next_window_carbon = Carbon::now();

        //To determine which week we are in the current month, as a factor
        $week_num = Carbon::now()->weekOfMonth;

        //To be noted that most months technically have 5 weeks, i.e.,
        //the last week is incomplete but has a few days. For the
        //sake of our calculation, we observe this 5th week.

        $next_window_carbon = $this->getSuccessfulCallTimeOffset($week_num, $next_window_carbon);

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times. 
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, $next_window_carbon);

        //String to facilitate testing

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        //$next_contact_windows = (new PatientContactWindow)->getNextWindowsForPatientFromDate($this->patient, Carbon::parse($next_predicted_contact_window['day']));

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($this->patient);

        //Call Info

        $patient_situation = $this->createSchedulerInfoString($week_num, $next_predicted_contact_window['day'], true, $window_start, $window_end);


        $prediction = [
            'predicament' => $patient_situation,
            'patient' => $this->patient,
            'date' => $next_predicted_contact_window['day'],
            'window' => $window,
            'window_start' => $window_start,
            'window_end' => $window_end,
            //'next_contact_windows' => $next_contact_windows,
            'successful' => true,
        ];

        $prediction = $this->formatAlgoDataForView($prediction);

        return $prediction;
    }

    public function unsuccessfulCallHandler(){

        //Update and close previous call, if exists.
        if($this->call) {

            $this->call->status = 'not reached';
            $this->call->note_id = $this->note->id;
            $this->call->called_date = Carbon::now()->toDateTimeString();
            $this->call->outbound_cpm_id = Auth::user()->ID;
            $this->call->save();

        } else {

            (new NoteService)->storeCallForNote($this->note, 'not reached', $this->patient, Auth::user(), 'outbound', 'algorithm');

        }

        //Here, we try to get the earliest contact window for the current patient,
        //and then determine the number of weeks to offset the next call by with
        //some analysis of the patient's current factors.

        //FACTORS

        //Set current day
        $next_window_carbon = Carbon::now();

        //To determine which week we are in the current month, as a factor
        $week_num = Carbon::now()->weekOfMonth;

        //To be noted that most months technically have 5 weeks, i.e.,
        //the last week is incomplete but has a few days. For the
        //sake of our calculation, we observe this 5th week.

//        $no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());
//        $hasHadASuccessfulCall = $no_of_successful_calls > 0 ? true : false;

        $next_window_carbon = $this->getUnsuccessfulCallTimeOffset($week_num, $next_window_carbon);

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, $next_window_carbon);

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        //$next_contact_windows = (new PatientContactWindow)->getNextWindowsForPatientFromDate($this->patient, Carbon::parse($next_predicted_contact_window['day']));

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($this->patient);

        $patient_situation = $this->createSchedulerInfoString($week_num, $next_predicted_contact_window['day'], false, $window_start, $window_end);

        //Call Info

        $prediction = [
            'predicament' => $patient_situation,
            'patient' => $this->patient,
            'date' => $next_predicted_contact_window['day'],
            'window' => $window,
            'window_start' => $window_start,
            'window_end' => $window_end,
            //'next_contact_windows' => $next_contact_windows,
            'successful' => false
        ];

        $prediction = $this->formatAlgoDataForView($prediction);

        return $prediction;
    }

    /*
     Handle missed/dropped/unattempted calls as a job
     This is not in the algo since we don't have
     a not associated to this kind of call
    */

    public function reconcileDroppedCallHandler(){

//        Update and close previous call, if exists.
        if($this->call) {

            $this->call->status = 'dropped';
            $this->call->scheduler = 'algorithm';
            $this->call->save();

        } else {

            (new NoteService)->storeCallForNote($this->note, 'dropped', $this->patient, null, 'outbound', 'algorithm');

        }

        //Call missed, call on next available call window.

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, Carbon::now());

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');
        $day = Carbon::parse($next_predicted_contact_window['day'])->toDateString();

        return (new SchedulerService())->storeScheduledCall($this->patient->ID, $window_start, $window_end, $day, Auth::user()->ID);

    }

    /*
    The next two functions will give us the time we have to wait until making the next
       attempt at reaching a patient
    */

    public function getSuccessfulCallTimeOffset($week_num, $next_window_carbon){

        if($this->ccmTime > 1199){ // More than 20 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in the second last week of the month
                return $next_window_carbon->endOfMonth()->subWeek(2);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in first week of next month
                return $next_window_carbon->addMonth(1)->firstOfMonth();

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
                return $next_window_carbon->addWeek(2);

            }

        }

        else if ($this->ccmTime > 899){ // 15 - 20 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in the second last week of the month
                //Note, might result in very close calls if second week.
                return $next_window_carbon->addWeek(2);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in last week of month
                return $next_window_carbon->endOfMonth()->subWeek(2);

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
                return $next_window_carbon->addWeek(2);

            }

            //Logic: Add 3 weeks or

        }

        else if ($this->ccmTime > 599){ // 10 - 15 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in 2 weeks.
                return $next_window_carbon->addWeek(2);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in first week of next month
                return $next_window_carbon->addWeek(1);

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after one week
                return $next_window_carbon->addWeek(1);

            }

        }

        else { // 0 - 10 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in the second last week of the month
                return $next_window_carbon->addWeek(1);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in first week of next month
                return $next_window_carbon->addWeek(1);

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
                return $next_window_carbon->addWeek(1);

            }

        }

        //If nothing matches, just return the same date
        return $next_window_carbon;

    }

    public function getUnsuccessfulCallTimeOffset($week_num, Carbon $next_window_carbon){

        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());

        if($this->ccmTime > 1199){ // More than 20 mins

            if($successfulCallsThisMonth > 0){ //If there was a successful call this month...

                //First window of next month
                return $next_window_carbon->addMonth(1)->firstOfMonth();

            } else {

                //try to connect in the weekend
                return $next_window_carbon->next(Carbon::SATURDAY);

            }

        }

        else if ($this->ccmTime > 899){ // 15 - 20 mins

            if($successfulCallsThisMonth > 0){ //If there was a successful call this month

                //Logic: Call patient in last week of month
                return $next_window_carbon->endOfMonth()->subWeek(2);

            } else

                //Call after a week
                return $next_window_carbon->addWeek(1);

            }

        else if ($this->ccmTime > 599){ // 10 - 15 mins

            if($successfulCallsThisMonth > 0) { //If there was a successful call this month

                if ($week_num < 4) { // We are in the first three weeks of the month

                    //Logic: Call patient in 1 weeks.
                    return $next_window_carbon->addWeek(1);

                } else if ($week_num == 4 || $week_num == 5) { //next day

                    //Logic: Call patient in first week of next month
                    return $next_window_carbon->tomorrow();

                }

            } else {

                if ($week_num < 4) { // We are in the first three weeks of the month

                    //Logic: Call patient in 1 weeks.
                    return $next_window_carbon->addWeek(1); //@todo implement low priority

                } else if ($week_num == 4 || $week_num == 5) { //next day

                    //Logic: Call patient in first week of next month
                    return $next_window_carbon->tomorrow();

                }


            }

        }

        else { // 0 - 10 mins

            if($successfulCallsThisMonth > 0) { //If there was a successful call this month

                //Logic: Call patient in 1 weeks.
                return $next_window_carbon->addWeek(1);

            } else {

                return $next_window_carbon->addWeek(1); // @todo implement low priority


            }

        }

        //If nothing matches, just return the same date
        return $next_window_carbon;

    }

    //Algo helpers and formatters

    public function createSchedulerInfoString($week_num, $next_window_carbon, $success, $window_start, $window_end){

        $status = '<span style="color: red">unsuccessfully</span>';

        if($success == true){

            $status = '<span style="color: green">successfully</span>';

        }

        //Abdullah Z-Ryan was called successfully in week 1. Patient's next predicted call window is: 2016-09-13 (9:00 am to 6:00 pm).
        
        return
            'You just called ' . $this->patient->fullName
            . ' '. $status .' in <b>week '
            . $week_num . '. </b> <br/> <br/> <b>'
            . 'Please confirm or amend the above next predicted call time. </b>';
//            . $next_window_carbon
//            . ' (' . Carbon::parse($window_start)->format('g:i a'). ' to '
//            . Carbon::parse($window_end)->format('g:i a') . ')</b>.' ;
    }

    //Currently returns ccm_time, no of calls, no of succ calls, patient call time prefs, week#
    public function formatAlgoDataForView($prediction){

        $ccm_time_achieved = false;
        if($this->ccmTime >= 1200){
            $ccm_time_achieved = true;
        }

        $H = floor($this->ccmTime / 3600);
        $i = ($this->ccmTime / 60) % 60;
        $s = $this->ccmTime % 60;
        $formattedMonthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

        //**CCM TIME**//
        // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs

        //$callsThisMonth = Call::numberOfCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());
        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());

        $prediction['no_of_successful_calls'] = $successfulCallsThisMonth;
        //$prediction['no_of_calls'] = $callsThisMonth;
        //$prediction['success_percent'] = ($successfulCallsThisMonth == 0 || $callsThisMonth == 0) ? 0 : ( ($successfulCallsThisMonth) / ($callsThisMonth) ) * 100;
        $prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $prediction['formatted_monthly_time'] = $formattedMonthlyTime;

        return $prediction;
    }

}