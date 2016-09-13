<?php namespace App\Services\Calls;


use App\Activity;
use App\Algorithms\Calls\PredictCall;
use App\Call;
use App\Note;
use App\PatientContactWindow;
use App\PatientInfo;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class SchedulerService
{

    //nurse mapping for import csv
    protected $nurses = [
        'Patricia' => 1920,
        'Katie' => 2159,
        'Lydia' => 1755,
        'Sue' => 1877,
        'Monique' => 2332,
    ];

    /* Success is the call's status.
       true for reached, false for not reached */

    public function getNextCall($patient, $noteId, $success)
    {

        //Collect last known scheduled call
        $scheduled_call = $this->getScheduledCallForPatient($patient);

        $note = Note::find($noteId);

        //Updates Call Record
        PatientMonthlySummary::updateCallInfoForPatient($patient->patientInfo, $success);

        return (new PredictCall($patient, $scheduled_call, $success))->predict($note);

    }

    //Create new scheduled call
    public function storeScheduledCall($patientId, $window_start, $window_end, $date, $scheduler, $nurse_id = false)
    {

        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end = Carbon::parse($window_end)->format('H:i');

        return Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'scheduler' => $scheduler,

            'inbound_phone_number' => $patient->phone ? $patient->phone : '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $patient->ID,
            'outbound_cpm_id' => isset($nurse_id) ? $nurse_id : '',

            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'scheduled_date' => $date,
            'window_start' => $window_start,
            'window_end' => $window_end,

            'is_cpm_outbound' => true

        ]);
    }

    //extract the last scheduled call
    public function getScheduledCallForPatient($patient)
    {

        $call = Call::where(function ($q) use ($patient) {
            $q->where('outbound_cpm_id', $patient->ID)
                ->orWhere('inbound_cpm_id', $patient->ID);
        })
            ->where('status', '=', 'scheduled')
            ->first();

        return $call;
    }

    public static function getUnAttemptedCalls()
    {

        $calls = Call::whereStatus('scheduled')
            ->where('scheduled_date', '<=', Carbon::now()->toDateString())->get();

        $missed = array();

        /*
         * Check to see if the call is dropped if it's the current day
         * Since we store the date and times separately for other
         * considerations, we have to join them and compare
         * to see if a call was missed on the same day
        */

        foreach ($calls as $call) {

            $end_carbon = Carbon::parse($call->scheduled_date);

            $carbon_hour_end = Carbon::parse($call->window_end)->format('H');
            $carbon_minutes_end = Carbon::parse($call->window_end)->format('i');

            $end_time = $end_carbon->setTime($carbon_hour_end, $carbon_minutes_end)->toDateTimeString();

            $now_carbon = Carbon::now()->toDateTimeString();

            if ($end_time < $now_carbon) {
                $missed[] = $call;
            }

        }

        return $missed;

    }

    public function removeScheduledCallsForWithdrawnPatients(){

        //get all patients that are withdrawn
        $withdrawn = PatientInfo::where('ccm_status', 'withdrawn')
            ->lists('user_id');

        $withdrawn_patients_with_calls = array();

        //get scheduled calls for them, if any, and delete them.
        foreach ($withdrawn as $patient){
            $temp = $this->getScheduledCallForPatient(User::find($patient));

            if(is_object($temp)){
                $withdrawn_patients_with_calls[] = $temp;
                $temp->delete();
            }
        }
    }

    public function importCallsFromCsv($csv)
    {
        $failed = [];

        foreach ($csv as $patient) {

            $temp = User::where('first_name', $patient['Patient First Name'])
                ->where('last_name', $patient['Patient Last Name'])
                ->whereHas('patientInfo', function ($q) use ($patient) {
                    $q->where('birth_date',
                        Carbon::parse($patient['DOB'])->toDateString()
                    );
                })
                ->first();

            if (is_object($temp)) {

                $patientContactWindow = $temp->patientInfo->patientContactWindows;

                if($temp->patientInfo->patientContactWindows->count() < 1){

                    if($patient[' Call preference (Day)'] != '') {

                        $days = explode(', ', $patient[' Call preference (Day)'] );

                        foreach ($days as $day){

                            PatientContactWindow::create([

                                'patient_info_id' => $temp->patientInfo->id,
                                'day_of_week' => Carbon::parse($day)->dayOfWeek + 1,
                                'window_time_start' => Carbon::parse($patient['Call time From:'])->format('H:i'),
                                'window_time_end' => Carbon::parse($patient['Call time to:'])->format('H:i'),

                            ]);

                        }

                    } else {

                        for($i = 1; $i < 5; $i++){

                            PatientContactWindow::create([

                                'patient_info_id' => $temp->patientInfo->id,
                                'day_of_week' => $i,
                                'window_time_start' => '09:00',
                                'window_time_end' => '17:00',

                            ]);

                        }

                    }

                }

                $call = $this->getScheduledCallForPatient($temp);

                Call::updateOrCreate([

                    'service' => 'phone',
                    'status' => 'scheduled',

                    'inbound_phone_number' => $temp->phone ? $temp->phone : '',
                    'outbound_phone_number' => '',

                    'inbound_cpm_id' => $temp->ID,
                    'outbound_cpm_id' => $this->nurses[$patient['Nurse']],

                    'call_time' => 0,

                    'is_cpm_outbound' => true

                ], [

                    'scheduled_date' => Carbon::parse($patient['Next call date'])->toDateString(),

                    'window_start' => empty($patient['Call time From:'])
                        ? '09:00'
                        : Carbon::parse($patient['Call time From:'])->format('H:i'),

                    'window_end' => empty($patient['Call time to:'])
                        ? '17:00'
                        : Carbon::parse($patient['Call time to:'])->format('H:i'),
                ]);

                $calls[] = $call;
            } else {
                $failed[] = "{$patient['Patient First Name']} {$patient['Patient Last Name']}";
            }

        };

        return $failed;
    }

    /* This solve the issue where a call is scheduled but RN spends
    CCM time doing other work after the call is over and note
    is saved */
    public function reprocessScheduledCallsFromCCMTime(){

        $patients = PatientInfo::enrolled()->get();

        $reprocess_bucket = [];

        foreach ($patients as $patient){

            $last_note_time = Activity::whereType('Patient Note Creation')->wherePatientId($patient->user_id)->pluck('created_at');
            $last_activity_time = Activity::wherePatientId($patient->user_id)->pluck('created_at');

            if(is_object($last_note_time) && is_object($last_activity_time)){

                if($last_note_time < $last_activity_time){
                    $reprocess_bucket[] = 'Patient with id ' . $patient->user_id . ' has to be reprocessed';
                }
            }
        }   

        return $reprocess_bucket;

    }

}