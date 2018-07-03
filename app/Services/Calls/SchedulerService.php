<?php namespace App\Services\Calls;

use App\Activity;
use App\Algorithms\Calls\SuccessfulHandler;
use App\Algorithms\Calls\UnsuccessfulHandler;
use App\Call;
use App\Family;
use App\Note;
use App\Nurse;
use App\Patient;
use App\Repositories\PatientWriteRepository;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SchedulerService
{
    private $patientWriteRepository;
    /**
     * @var NoteService
     */
    private $noteService;

    /**
     * SchedulerService constructor.
     *
     * @param PatientWriteRepository $patientWriteRepository
     */
    public function __construct(PatientWriteRepository $patientWriteRepository, NoteService $noteService)
    {
        $this->patientWriteRepository = $patientWriteRepository;
        $this->noteService            = $noteService;
    }

    /* Success is the call's status.
       true for reached, false for not reached */
    public function getNextCall(
        $patient,
        $noteId,
        $success
    ) {

        $isComplex = $patient->isCCMComplex();

        //Collect last known scheduled call
        $scheduled_call = $this->getScheduledCallForPatient($patient);

        $note = Note::find($noteId);

        //find previously scheduled call and update it with attempt results as reached or not reached.
        $this->updateOrCreatePreviousScheduledCall(
            $note,
            $scheduled_call,
            $success
                ? 'reached'
                : 'not reached'
        );

        $this->patientWriteRepository->updateCallLogs($patient->patientInfo, $success);

        //last attempt
        $previousCall = $this->getPreviousCall($patient, $scheduled_call['id']);

        if ($success) {
            $prediction = (new SuccessfulHandler($patient->patientInfo, Carbon::now(), $isComplex,
                $previousCall))->handle();

            $prediction['successful'] = true;

            return $prediction;
        }

        $prediction = (new UnsuccessfulHandler($patient->patientInfo, Carbon::now(), $isComplex,
            $previousCall))->handle();

        $prediction['successful'] = false;

        return $prediction;
    }

    //Get scheduled call
    public function getScheduledCallForPatient($patient)
    {

        $call = Call::where(function ($q) use (
            $patient
        ) {
            $q->where('outbound_cpm_id', $patient->id)
              ->orWhere('inbound_cpm_id', $patient->id);
        })
                    ->where('status', '=', 'scheduled')
                    ->first();

        return $call;
    }

    //Updates previous call
    public function updateOrCreatePreviousScheduledCall(
        Note $note,
        $call,
        $status
    ) {

        $patient = $note->patient;

        if ($call) {
            $call->status          = $status;
            $call->note_id         = $note->id;
            $call->called_date     = Carbon::now()->toDateTimeString();
            $call->outbound_cpm_id = Auth::user()->id;
            $call->save();
        } else { // If call doesn't exist, make one and store it

            $this->noteService->storeCallForNote(
                $note,
                $status,
                $patient,
                Auth::user(),
                'outbound',
                'core algorithm'
            );
        }
    }

    //extract the last scheduled call

    public function getPreviousCall($patient, $scheduled_call_id)
    {

        //be careful not to consider call just made,
        //since algo already updates it before getting here.
        //check for day != today

        $call = Call
            ::where('inbound_cpm_id', $patient->id)
            ->where('status', '!=', 'scheduled')
            ->where('called_date', '!=', '')
//            ->where('id', '!=', $scheduled_call_id)
            ->orderBy('called_date', 'desc')
            ->first();

        return $call;
    }

    public function storeScheduledCall(
        $patientId,
        $window_start,
        $window_end,
        $date,
        $scheduler,
        $nurse_id = null,
        $attempt_note = ''
    ) {

        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end   = Carbon::parse($window_end)->format('H:i');

        $nurse_id = ($nurse_id == '')
            ? null
            : $nurse_id;

        $call = Call::create([

            'service' => 'phone',
            'status'  => 'scheduled',

            'attempt_note' => $attempt_note,

            'scheduler' => $scheduler,

            'inbound_phone_number' => $patient->patientInfo->phone
                ? $patient->patientInfo->phone
                : '',

            'outbound_phone_number' => '',

            'inbound_cpm_id'  => $patient->id,
            'outbound_cpm_id' => $nurse_id,

            'call_time'  => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'scheduled_date' => $date,
            'window_start'   => $window_start,
            'window_end'     => $window_end,

            'is_cpm_outbound' => true,

        ]);

        return $call;
    }

    public function removeScheduledCallsForWithdrawnAndPausedPatients()
    {

        //get all patients that are withdrawn
        $withdrawn = Patient::whereIn('ccm_status', [
            Patient::WITHDRAWN,
            Patient::PAUSED,
            Patient::UNREACHABLE,
        ])
                            ->pluck('user_id')
                            ->all();

        return Call::where(function ($q) use (
            $withdrawn
        ) {
            $q->whereIn('outbound_cpm_id', $withdrawn)
              ->orWhereIn('inbound_cpm_id', $withdrawn);
        })
                   ->where('status', '=', 'scheduled')
                   ->delete();
    }

    public function importCallsFromCsv($csv)
    {
        $failed = [];
        foreach ($csv as $row) {
            $patient = User::where('first_name', $row['Patient First Name'])
                           ->where('last_name', $row['Patient Last Name'])
                           ->whereHas('patientInfo', function ($q) use (
                               $row
                           ) {
                               $q->where(
                                   'birth_date',
                                   Carbon::parse($row['DOB'])->toDateString()
                               );
                           })
                           ->first();

            if ( ! $patient) {
                $failed[] = "{$row['Patient First Name']} {$row['Patient Last Name']}";
                continue;
            }

            $days = parseDaysStringToNumbers($row['Call preference (Day)']);

            $fromTime = $row['Call time From:'];
            $toTime   = $row['Call time to:'];

            $info = $patient->patientInfo;

            $callWindows = $info->attachNewOrDefaultCallWindows($days, $fromTime, $toTime);

            if (array_key_exists('General Comment', $row)) {
                $generalComment = $row['General Comment'];
            }

            if ( ! empty($generalComment)) {
                $info->general_comment = $generalComment;
                $info->save();
            }

            $call = $this->getScheduledCallForPatient($patient);

            Call::updateOrCreate([

                'service' => 'phone',
                'status'  => 'scheduled',

                'inbound_phone_number'  => $patient->phone
                    ? $patient->phone
                    : '',
                'outbound_phone_number' => '',

                'inbound_cpm_id'  => $patient->id,
                'outbound_cpm_id' => Nurse::$nurseMap[$row['Nurse']],

                'call_time' => 0,

                'is_cpm_outbound' => true,

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

    public function syncFamilialCalls()
    {

        $families    = Family::all();
        $familyCalls = [];

        foreach ($families as $family) {
            //First get family members
            $patients = $family->patients()->get();

            //Then get their family's calls
            $scheduledDates  = [];
            $scheduledCalls  = [];
            $designatedNurse = null;

            $window_start = '09:00:00';
            $window_end   = '17:00:00';

            foreach ($patients as $patient) {
                $call = $this->getScheduledCallForPatient($patient->user);

                if (is_object($call)) {
                    //If the patient has a call,

                    $window_start = $call->window_start;
                    $window_end   = $call->window_end;

                    $date = Carbon::parse($call->scheduled_date);
                    $date->setTimeFromTimeString(Carbon::parse($call->window_start)->toTimeString());

                    //Set one of the nurses as the designated nurse
                    $designatedNurse = $call->outbound_cpm_id;

                    //ignore if past call
                    if (Carbon::now()->toDateTimeString() < $date->toDateTimeString()) {
                        $scheduledDates[$patient->user_id] = $date->toDateTimeString();
                    }
                } else {
                    //fill in some call info:
                    $call = Call::create([

                        'service' => 'phone',
                        'status'  => 'scheduled',

                        'attempt_note' => '',

                        'scheduler' => 'family algorithm',

                        'inbound_cpm_id' => $patient->user_id,

                        'outbound_phone_number' => '',
                        'is_cpm_outbound'       => true,

                        'call_time'  => 0,
                        'created_at' => Carbon::now()->toDateTimeString(),

                    ]);
                }

                $scheduledCalls[$patient->user_id] = $call;
            }

            if ( ! empty($scheduledDates)) {
                //determine minimum date
                $minDate = Carbon::parse(min($scheduledDates));

                //patientId => patientScheduledCall
                foreach ($scheduledCalls as $key => $value) {
                    $callPatient = User::find($key);

                    $value->scheduled_date       = $minDate->toDateTimeString();
                    $value->inbound_phone_number = $callPatient->phone
                        ? $callPatient->phone
                        : '';
                    $value->outbound_cpm_id      = $designatedNurse;
                    $value->window_start         = $window_start;
                    $value->window_end           = $window_end;
                    $value->save();
                }
                $familyCalls[] = $scheduledCalls;
            }
        }

        return $familyCalls;
    }

    public function tuneScheduledCallsWithUpdatedCCMTime()
    {

        //Get all enrolled Patients
        $patients = Patient::enrolled()->get();

        $reprocess_bucket = [];

        foreach ($patients as $patient) {

            if ( ! $patient->user) {
                continue;
            }

            //Get time for last note entered
            $last_note_time = Activity::whereType('Patient Note Creation')
                                      ->wherePatientId($patient->user_id)
                                      ->orderBy('created_at', 'desc')
                                      ->pluck('created_at')
                                      ->first();

            //Get time for last activity recorded
            $last_activity_time = Activity::wherePatientId($patient->user_id)
                                          ->orderBy('created_at', 'desc')
                                          ->pluck('created_at')
                                          ->first();

            //check if they both exist
            if ($last_note_time != null && $last_activity_time != null) {
                //then check if the note was made before the last activity
                if ($last_note_time < $last_activity_time) {
                    try {
                        //have to pull the last scheduled call, but only if it was made by the algo
                        //since we don't mess with calls scheduled manually
                        $scheduled_call = $patient->user->inboundCalls()
                                                        ->where('status', 'scheduled')
                                                        ->where('scheduler', 'algorithm')
                                                        ->first();

                        $last_attempted_call = $patient->user->inboundCalls()
                                                             ->where('status', '!=', 'scheduled')
                                                             ->orderBy('created_at', 'desc')
                                                             ->first();
                    } catch (\Exception $exception) {
                        \Log::critical($exception);
                        \Log::info("Patient Info Id $patient->id");
                        continue;
                    }

                    //make sure we have a call attempt and a scheduled call.
                    if (is_object($scheduled_call) && is_object($last_attempted_call)) {
                        $status = ($last_attempted_call->status == 'reached')
                            ? true
                            : false;

                        $last_attempted_time = $last_attempted_call->called_date;

                        if ($status) {
                            $data = (new SuccessfulHandler($patient, Carbon::parse($last_attempted_time),
                                $patient->user->isCCMComplex(), $last_attempted_call));
                        } else {
                            $data = (new UnsuccessfulHandler($patient, Carbon::parse($last_attempted_time),
                                $patient->user->isCCMComplex(), $last_attempted_call));
                        }

                        $scheduled_call->scheduler      = 'refresher algorithm';
                        $scheduled_call->scheduled_date = $data['date'];
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
