<?php namespace App\Algorithms\Calls;

use App\Call;
use App\PatientContactWindow;
use App\PatientInfo;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PredictCall
{
    //In order to predict a call, a patient's factors are taken into consideration.
    //These factors are the different switches that determine the best next
    //plausible call date for a patient. Successful calls are handled
    //differently from unsuccessful calls.

    // ------------------------------------*---------------------------------------
    // Currently, the factors taken into consideration are:
    //  - Call Status (Reached, Unreached)
    //  - Current Month's CCM Time Bracket (0-10, 10-15, 15-20, >20)
    //  - Week Number in current Month (1, 2, 3, 4) [Special Consideration for months with 5 weeks: (1, 2, 3+4, 5)]
    //  - No Of Successful Calls to Patient
    // ------------------------------------*---------------------------------------


    //Currently returns ccm_time, no of calls, no of succ calls, patient call time prefs, week#
    public function getAlgorithmFactors(User $patient, $prediction){

        //**CALLS**//
        $no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth($patient,Carbon::now()->toDateTimeString());
        $no_of_calls = Call::numberOfCallsForPatientForMonth($patient,Carbon::now()->toDateTimeString());

        if($no_of_successful_calls == 0 || $no_of_calls == 0){
            $success_percent = 'N/A';
        }
        else {
            $success_percent = ( ($no_of_successful_calls) / ($no_of_calls) ) * 100;
        }

        //**CCM TIME**//
        // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs
        $seconds = $patient->patientInfo()->first()->cur_month_activity_time;

        $ccm_time_achieved = false;
        if($seconds >= 1200){
            $ccm_time_achieved = true;
        }

        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        $formattedMonthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

        $prediction['no_of_successful_calls'] = $no_of_successful_calls;
        $prediction['no_of_calls'] = $no_of_calls;
        $prediction['success_percent'] = $success_percent;
        $prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $prediction['formatted_monthly_time'] = $formattedMonthlyTime;

        return $prediction;
    }

    public function successfulCallHandler($patient, $note, $scheduled_call){

        //Update and close previous call
        if($scheduled_call) {

            $scheduled_call->status = 'reached';
            $scheduled_call->note_id = $note->id;
            $scheduled_call->call_date = Carbon::now()->format('Y-m-d');
            $scheduled_call->outbound_cpm_id = Auth::user()->ID;
            $scheduled_call->save();

        } else { // If call doesn't exist, make one and store it

            (new NoteService)->storeCallForNote($note, 'reached', $patient, Auth::user(), 'outbound');

        }



        //Here, we try to get the earliest contact window for the current patient,
        //and then determine the number of weeks to offset the next call by with
        //some analysis of the patient's current factors.

        //FACTORS

        //To get initial date to offset
        $next_contact_window = (new PatientContactWindow())->getEarliestWindowForPatient($patient);
        $next_window_carbon = Carbon::parse($next_contact_window['day']);

        //To determine which week we are in the current month, as a factor
        $week_num = Carbon::now()->weekOfMonth;

        $ccm_time = $patient->patientInfo()->first()->cur_month_activity_time;

        if($ccm_time > 1199){ // More than 20 mins

            if($week_num == 1 || $week_num == 2){ // We are in the first two weeks of the month

                //Logic: Call patient in the second last week of the month
                $next_window_carbon = $next_window_carbon->endOfMonth()->subWeek(1);

            } else if ($week_num == 3 || $week_num == 4 ){ //second last week of month
                
                //Logic: Call patient in first week of next month
                $next_window_carbon = $next_window_carbon->next('month')->firstOfMonth();
                
            } else if ($week_num == 4 || $week_num == 5 ){ //last-ish week of month

                //Logic: Call patient after two weeks
                $next_window_carbon = $next_window_carbon->addWeek(2);

            }

        } else if ($ccm_time > 899){ // 15 - 20 mins

            if($week_num == 1){

            }

            //Logic: Add 3 weeks or

        } else if ($ccm_time > 599){ // 10 - 15 mins


        } else { // 0 - 10 mins


        }

        //the day we can use to start predicting the next call date, and subsequent windows.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($patient, $next_window_carbon);
        
        //String to facilitate testing
        $patient_situation = $patient->fullName . 'was called successfully in <b>week '
                                . $week_num . ' </b> and has <b>ccm time: ' . intval($ccm_time/60) . ' mins </b>. ' .
                                  'His predicted next call date is: <b>' . Carbon::parse($next_predicted_contact_window['day'])->toDateTimeString()
                                    . '</b>' ;

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        $next_contact_windows = (new PatientContactWindow)->getNextWindowsForPatientFromDate($patient, Carbon::parse($next_predicted_contact_window['day']));

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        //Call Info

        $prediction = [
            'predicament' => $patient_situation,
            'patient' => $patient,
            'date' => $next_predicted_contact_window['day'],
            'window' => $window,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => true
        ];

        $prediction = $this->getAlgorithmFactors($patient, $prediction);

        return $prediction;
    }

    public function unsuccessfulCallHandler($patient, $note, $scheduled_call){

        //Update and close previous call, if exists.
        if($scheduled_call) {
            $scheduled_call->status = 'not reached';
            $scheduled_call->note_id = $note->id;
            $scheduled_call->call_date = Carbon::now()->format('Y-m-d');
            $scheduled_call->outbound_cpm_id = Auth::user()->ID;
            $scheduled_call->save();

        } else {

            (new NoteService)->storeCallForNote($note, 'not reached', $patient, Auth::user(), 'outbound');

        }

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = Carbon::parse($patient_preferred_times['window_start'])->format('H:i');
        $window_end = Carbon::parse($patient_preferred_times['window_end'])->format('H:i');

        //Schedule call for tomorrow.
        $earliest_contact_day = Carbon::now()->addDay();

        $next_contact_windows = PatientContactWindow::getNextWindowsForPatient($patient);

        $prediction =  [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => false
        ];

        $prediction = $this->getAlgorithmFactors($patient, $prediction);

        return $prediction;

    }

}