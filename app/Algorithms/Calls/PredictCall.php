<?php namespace App\Algorithms\Calls;

use App\Call;
use App\PatientContactWindow;
use App\Services\Calls\SchedulerService;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait PredictCall
{

    


    
    public function reconcileDroppedCallHandler()
    {

        $this->call->status = 'dropped';
        $this->call->scheduler = 'rescheduler algorithm';
        $this->call->save();


        //Call missed, call on next available call window.

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient, Carbon::now());

        $window_start = Carbon::parse($next_predicted_contact_window['window_start'])->format('H:i');
        $window_end = Carbon::parse($next_predicted_contact_window['window_end'])->format('H:i');
        $day = Carbon::parse($next_predicted_contact_window['day'])->toDateString();

        return (new SchedulerService())->storeScheduledCall($this->patient->ID,
            $window_start,
            $window_end,
            $day,
            'rescheduler algorithm',
            $this->call->outbound_cpm_id
        );

    }


    

}