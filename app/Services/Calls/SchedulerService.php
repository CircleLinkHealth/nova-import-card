<?php namespace App\Services\Calls;


use App\Call;
use App\Note;
use App\PatientContactWindow;
use App\PatientInfo;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SchedulerService
{

    //This function will be the initial rendition of algorithm
    //to predict the patient's next call date.
    //It will take the patient's User object,
    //the Note and the outcome of the call
    //as input and return a datetime

    public function predictCall($patient, $note ,$success)
    {

        $scheduled_call = $this->getScheduledCallForPatient($patient);

        $note = Note::find($note);

        if ($success) {

            return $this->successfulCallHandler($patient, $note, $scheduled_call);

        } else {

            return $this->unsuccessfulCallHandler($patient, $note, $scheduled_call);

        }
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

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        return [
            'patient' => $patient,
            'date' => $next_contact_window['day'],
            'window' => $window,
            //give it the start time for now...
            'window_start' => $window_start,
            'window_end' => $window_end,
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

        $window_start = Carbon::parse($patient_preferred_times['window_start'])->format('H:i:s');
        $window_end = Carbon::parse($patient_preferred_times['window_end'])->format('H:i:s');

        $earliest_contact_day = Carbon::now()->addDay()->format('Y-m-d');

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

//      $this->storeScheduledCall($patient->ID, $window_start, $window_end, $earliest_contact_day);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => false
        ];
    }

    //Create new scheduled call
    public function storeScheduledCall($patientId, $window_start, $window_end, $date)
    {

        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end = Carbon::parse($window_end)->format('H:i');

        return Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'inbound_phone_number' => $patient->phone ? $patient->phone : '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $patient->ID,

            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'call_date' => $date,
            'window_start' => $window_start,
            'window_end' => $window_end,

            'is_cpm_outbound' => true

        ]);
    }

    //extract the last scheduled call
    public function getScheduledCallForPatient($patient){

        $call = Call::where(function($q) use ($patient)
        {
            $q->where('outbound_cpm_id', $patient->ID)
            ->orWhere('inbound_cpm_id', $patient->ID);
        })
            ->where('status', '=' , 'scheduled')
            ->first();

        return $call;
    }

}