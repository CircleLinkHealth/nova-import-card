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

    public function getPatientCallInfo(User $patient, $prediction){

        $no_of_successful_calls = Call::numberOfSuccessfulCallsForPatientForMonth($patient,Carbon::now()->toDateTimeString());
        $no_of_calls = Call::numberOfCallsForPatientForMonth($patient,Carbon::now()->toDateTimeString());

        if($no_of_successful_calls == 0 || $no_of_calls == 0){
            $success_percent = 'N/A';
        } else {
            $success_percent = ( ($no_of_successful_calls) / ($no_of_calls) ) * 100;
        }

        // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs

        $seconds = $patient->patientInfo()->first()->cur_month_activity_time;

        $ccm_time_achieved = false;
        if($seconds >= 1200){
            $ccm_time_achieved = true;
        }

        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        $monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

        $prediction['no_of_successful_calls'] = $no_of_successful_calls;
        $prediction['no_of_calls'] = $no_of_calls;
        $prediction['success_percent'] = $success_percent;
        $prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $prediction['formatted_monthly_time'] = $monthlyTime;

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

        $next_contact_window = (new PatientContactWindow())->getEarliestWindowForPatient($patient);

        $window_start = Carbon::parse($next_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_contact_window['window_end'])->format('H:i');

        //TO CALCULATE

        $next_contact_windows = PatientContactWindow::getNextWindowsForPatient($patient);

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        //Call Info

        $prediction = [
            'patient' => $patient,
            'date' => $next_contact_window['day'],
            'window' => $window,
            //give it the start time for now...
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => true
        ];

        $prediction = $this->getPatientCallInfo($patient, $prediction);

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

        $prediction = $this->getPatientCallInfo($patient, $prediction);

        return $prediction;

    }
    
}