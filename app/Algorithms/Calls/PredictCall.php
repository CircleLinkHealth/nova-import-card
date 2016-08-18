<?php namespace App\Algorithms\Calls;

use App\Call;
use App\Http\Controllers\CallController;
use App\Note;
use App\PatientContactWindow;
use App\PatientInfo;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class PredictCall
{

    // ------------------------------------*---------------------------------------
    // Currently, the factors taken into consideration are:
    //  - Call Status (Reached, Unreached)
    //  - Current Month's CCM Time Bracket (0-10, 10-15, 15-20, >20)
    //  - Week Number in current Month (1, 2, 3, 4) [Special Consideration for months with 5 weeks: (1, 2, 3+4, 5)]
    //  - No Of Successful Calls to Patient
    // ------------------------------------*---------------------------------------

    private $call;
    private $patient;
    private $note;
    private $callsThisMonth;
    private $successfulCallsThisMonth;
    private $ccmTime;




    public function __construct(User $calledPatient, Note $currentNote, $currentCall)
    {

        $this->call = $currentCall;
        $this->patient = $calledPatient;
        $this->note = $currentNote;

        $this->callsThisMonth = Call::numberOfCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());
        $this->successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());

        $this->ccmTime = $this->patient->patientInfo->cur_month_activity_time;

    }

    //In order to predict a call, a patient's factors are taken into consideration.
    //These factors are the different switches that determine the best next
    //plausible call date for a patient. Successful calls are handled
    //differently from unsuccessful calls.


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

        $prediction['no_of_successful_calls'] = $this->successfulCallsThisMonth;
        $prediction['no_of_calls'] = $this->callsThisMonth;
        $prediction['success_percent'] = ($this->successfulCallsThisMonth == 0 || $this->callsThisMonth == 0) ? 0 : ( ($this->successfulCallsThisMonth) / ($this->callsThisMonth) ) * 100;
        $prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $prediction['formatted_monthly_time'] = $formattedMonthlyTime;

        return $prediction;
    }

    public function successfulCallHandler(){

        //Update and close previous call
        if($this->call) {

            $this->call->status = 'reached';
            $this->call->note_id = $this->note->id;
            $this->call->call_date = Carbon::now()->format('Y-m-d');
            $this->call->outbound_cpm_id = Auth::user()->ID;
            $this->call->save();

        } else { // If call doesn't exist, make one and store it

            (new NoteService)->storeCallForNote($this->note, 'reached', $this->patient, Auth::user(), 'outbound');

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

        $next_window_carbon = $this->getSuccessfulCallTimeOffset($this->ccmTime, $week_num, $next_window_carbon);

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times. 
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, $next_window_carbon);

        //String to facilitate testing
        $patient_situation = $this->patient->fullName . 'was called successfully in <b>week '
                                . $week_num . ' </b> and has <b>ccm time: ' . intval($this->ccmTime/60) . ' mins </b> (' . $this->ccmTime .
                                  ' seconds). He can be called starting <b>' . $next_window_carbon->toDateTimeString() . '</b> but his first window after that is: <b>' . $next_predicted_contact_window['day']
                                    . '</b>' ;

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        $next_contact_windows = (new PatientContactWindow)->getNextWindowsForPatientFromDate($this->patient, Carbon::parse($next_predicted_contact_window['day']));

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($this->patient);

        //Call Info

        $prediction = [
            'predicament' => $patient_situation,
            'patient' => $this->patient,
            'date' => $next_predicted_contact_window['day'],
            'window' => $window,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => true
        ];

        $prediction = $this->formatAlgoDataForView($prediction);

        return $prediction;
    }

    public function unsuccessfulCallHandler(){

        //Update and close previous call, if exists.
        if($this->call) {
            $this->call->status = 'not reached';
            $this->call->note_id = $this->note->id;
            $this->call->call_date = Carbon::now()->format('Y-m-d');
            $this->call->outbound_cpm_id = Auth::user()->ID;
            $this->call->save();

        } else {

            (new NoteService)->storeCallForNote($this->note, 'not reached', $this->patient, Auth::user(), 'outbound');

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

        $no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient,Carbon::now()->toDateTimeString());
        $hasHadASuccessfulCall = $no_of_successful_calls->count() > 0 ? true : false;

        $next_window_carbon = $this->getUnsuccessfulCallTimeOffset($this->ccmTime, $week_num, $next_window_carbon, $hasHadASuccessfulCall);

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, $next_window_carbon);

        //String to facilitate testing
        $patient_situation = $this->patient->fullName . 'was called successfully in <b>week '
            . $week_num . ' </b> and has <b>ccm time: ' . intval($this->ccmTime/60) . ' mins </b> (' . $this->ccmTime .
            ' seconds). He can be called starting <b>' . $next_window_carbon->toDateTimeString() . '</b> but his first window after that is: <b>' . $next_predicted_contact_window['day']
            . '</b>' ;

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        $next_contact_windows = (new PatientContactWindow)->getNextWindowsForPatientFromDate($this->patient, Carbon::parse($next_predicted_contact_window['day']));

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($this->patient);

        //Call Info

        $prediction = [
            'predicament' => $patient_situation,
            'patient' => $this->patient,
            'date' => $next_predicted_contact_window['day'],
            'window' => $window,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => true
        ];

        $prediction = $this->formatAlgoDataForView($prediction);

        return $prediction;
    }

    //The next two functions will give us the time we have to wait until making the next
    //attempt at reaching a patient

    public function getSuccessfulCallTimeOffset($ccm_time, $week_num, $next_window_carbon){

        if($ccm_time > 1199){ // More than 20 mins

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

        else if ($ccm_time > 899){ // 15 - 20 mins

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

        else if ($ccm_time > 599){ // 10 - 15 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in 2 weeks.
                return $next_window_carbon->addWeek(2);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in first week of next month
                return $next_window_carbon->addWeek(1);

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
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

    }

    public function getUnsuccessfulCallTimeOffset($ccm_time, $week_num, Carbon $next_window_carbon, $hasHadASuccessfulCall){

        if($ccm_time > 1199){ // More than 20 mins


        }

        else if ($ccm_time > 899){ // 15 - 20 mins

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

        else if ($ccm_time > 599){ // 10 - 15 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in 2 weeks.
                return $next_window_carbon->addWeek(2);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month

                //Logic: Call patient in first week of next month
                return $next_window_carbon->addWeek(1);

            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
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


    }

}