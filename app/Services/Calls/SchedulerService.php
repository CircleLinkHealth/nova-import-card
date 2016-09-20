<?php namespace App\Services\Calls;


use App\Activity;
use App\Algorithms\Calls\PredictCall;
use App\Call;
use App\Note;
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
        'Erin' => 2398,
        'Kerri' => 2012,
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
    public function storeScheduledCall($patientId, $window_start, $window_end, $date, $scheduler, $nurse_id = false, $attempt_note = '')
    {

        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end = Carbon::parse($window_end)->format('H:i');

        return Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'attempt_note' => $attempt_note,

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

    public function removeScheduledCallsForWithdrawnPatients()
    {

        //get all patients that are withdrawn
        $withdrawn = PatientInfo::where('ccm_status', 'withdrawn')
            ->lists('user_id');

        $withdrawn_patients_with_calls = array();

        //get scheduled calls for them, if any, and delete them.
        foreach ($withdrawn as $patient) {
            $temp = $this->getScheduledCallForPatient(User::find($patient));

            if (is_object($temp)) {
                $withdrawn_patients_with_calls[] = $temp;
                $temp->delete();
            }
        }
    }
    
    public function importCallsFromCsv($csv)
    {
        $failed = [];
        foreach ($csv as $row) {

            $patient = User::where('first_name', $row['Patient First Name'])
                ->where('last_name', $row['Patient Last Name'])
                ->whereHas('patientInfo', function ($q) use ($row) {
                    $q->where('birth_date',
                        Carbon::parse($row['DOB'])->toDateString()
                    );
                })
                ->first();

            if (!$patient) {
                $failed[] = "{$row['Patient First Name']} {$row['Patient Last Name']}";
                continue;
            }

            $days = parseDaysStringToNumbers($row['Call preference (Day)']);

            $fromTime = $row['Call time From:'];
            $toTime = $row['Call time to:'];

            $info = $patient->patientInfo;

            $callWindows = $info->attachNewOrDefaultCallWindows($days, $fromTime, $toTime);

            if (array_key_exists('General Comment', $row)) $generalComment = $row['General Comment'];

            if (!empty($generalComment)) {
                $info->general_comment = $generalComment;
                $info->save();
            }

            $call = $this->getScheduledCallForPatient($patient);

            Call::updateOrCreate([

                'service' => 'phone',
                'status' => 'scheduled',

                'inbound_phone_number' => $patient->phone ? $patient->phone : '',
                'outbound_phone_number' => '',

                'inbound_cpm_id' => $patient->ID,
                'outbound_cpm_id' => $this->nurses[$row['Nurse']],

                'call_time' => 0,

                'is_cpm_outbound' => true

            ], [

                'scheduled_date' => Carbon::parse($row['Next call date'])->toDateString(),

                'window_start' => empty($fromTime)
                    ? '09:00'
                    : Carbon::parse($fromTime)->format('H:i'),

                'window_end' => empty($toTime)
                    ? '17:00'
                    : Carbon::parse($toTime)->format('H:i'),
            ]);

            $calls[] = $call;

        }


        return $failed;
    }

    /* This solve the issue where a call is scheduled but RN spends
    CCM time doing other work after the call is over and note
    is saved */
    public function tuneScheduledCallsWithUpdatedCCMTime()
    {

        //Get all enrolled Patients
        $patients = PatientInfo::enrolled()->get();

        $reprocess_bucket = [];

        foreach ($patients as $patient) {

            //Get time for last note entered
            $last_note_time = Activity::whereType('Patient Note Creation')->wherePatientId($patient->user_id)->orderBy('created_at', 'desc')->pluck('created_at')->first();

            //Get time for last activity recorded
            $last_activity_time = Activity::wherePatientId($patient->user_id)->orderBy('created_at', 'desc')->pluck('created_at')->first();

            //check if they both exist
            if ($last_note_time != null && $last_activity_time != null) {

                //then check if the note was made before the last activity
                if ($last_note_time < $last_activity_time) {

                    //have to pull the last scheduled call, but only if it was made by the algo
                    //since we don't mess with calls scheduled manually
                    $scheduled_call = $patient->user->inboundCalls()->where('status', 'scheduled')->where('scheduler', 'algorithm')->first();
                    $last_attempted_call = $patient->user->inboundCalls()->where('status', '!=', 'scheduled')->orderBy('created_at', 'desc')->first();

                    //make sure we have a call attempt and a scheduled call.
                    if (is_object($scheduled_call) && is_object($last_attempted_call)) {

                        $status = ($last_attempted_call->status == 'reached') ? true : false;

                        //see how much time should wait now that the algo has updated information
                        $scheduled_call->scheduled_date = (new PredictCall($patient->user, $last_attempted_call, $status))
                            ->getUnsuccessfulCallTimeOffset(
                                Carbon::now()->weekOfMonth,
                                Carbon::now())
                            ->toDateString();

                        $scheduled_call->scheduler = 'refresher algorithm';
                        $scheduled_call->save();

                        $reprocess_bucket[] = 'Patient: ' . $patient->user_id . ' was tuned, will now be called on ' . $scheduled_call->scheduled_date;

                    }

                }
            }
        }

        return empty($reprocess_bucket)
            ? 'No Patients Need Refreshin\'!'
            : $reprocess_bucket;

    }

}