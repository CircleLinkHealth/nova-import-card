<?php namespace App\Algorithms\Calls;

use App\PatientContactWindow;
use App\PatientInfo;
use App\Services\NoteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PredictCall
{

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

        return [
            'patient' => $patient,
            'date' => $next_contact_window['day'],
            'window' => $window,
            //give it the start time for now...
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => true
        ];
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


        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window_start' => $window_start,
            'window_end' => $window_end,
            'next_contact_windows' => $next_contact_windows,
            'successful' => false
        ];
    }
    
}