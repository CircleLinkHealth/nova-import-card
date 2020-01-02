<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Calls;

use App\Algorithms\Calls\SuccessfulHandler;
use App\Algorithms\Calls\UnsuccessfulHandler;
use App\Call;
use App\Note;
use App\Repositories\PatientWriteRepository;
use App\Services\NoteService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Family;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Support\Facades\Auth;

class SchedulerService
{
    const CALL_BACK_TYPE = 'Call Back';

    const CALL_TYPE = 'call';

    /**
     * @var NoteService
     */
    private $noteService;
    private $patientWriteRepository;

    /**
     * SchedulerService constructor.
     */
    public function __construct(PatientWriteRepository $patientWriteRepository, NoteService $noteService)
    {
        $this->patientWriteRepository = $patientWriteRepository;
        $this->noteService            = $noteService;
    }

    public function ensurePatientHasScheduledCall(User $patient)
    {
        $call = $this->getScheduledCallForPatient($patient);
        if ($call) {
            return;
        }

        $now = Carbon::now();

        $patient->loadMissing('patientInfo');

        $next_predicted_contact_window = (new PatientContactWindow())->getEarliestWindowForPatientFromDate(
            $patient->patientInfo,
            $now
        );

        //by default we do not assign a nurse
        $nurseId = null;

        //get patient's assigned nurse for this window
        $patientNurses = $patient->patientInfo->getNurses();
        if ($patientNurses) {
            if (isset($patientNurses['temporary'])) {
                $temp = $patientNurses['temporary'];
                if ($now->isBetween($temp['from'], $temp['to'])) {
                    $nurseId = $temp['user']->id;
                }
            } else {
                $nurseId = $patientNurses['permanent']['user']->id;
            }
        } else {
            //if we do not have an assigned nurse, look for last call attempt
            /** @var Call $previousCall */
            $previousCall = $this->getPreviousCall($patient);
            if ($previousCall) {
                $nurseId = $previousCall->outbound_cpm_id;
            }
        }

        $this->storeScheduledCall(
            $patient->id,
            $next_predicted_contact_window['window_start'],
            $next_predicted_contact_window['window_end'],
            $next_predicted_contact_window['day'],
            'call checker algorithm',
            $nurseId,
            '',
            false
        );
    }

    public static function getNextScheduledCall($patientId, $excludeToday = false)
    {
        return Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', SchedulerService::CALL_TYPE);
        })
            ->where('inbound_cpm_id', $patientId)
            ->where('status', '=', 'scheduled')
            ->when($excludeToday, function ($query) {
                       $query->where('scheduled_date', '>', Carbon::today()->format('Y-m-d'));
                   }, function ($query) {
                       $query->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'));
                   })
            ->orderBy('scheduled_date', 'desc')
            ->first();
    }

    /**
     * Extract last attempt for a call.
     * Status should be 'reached' or 'not reached'.
     * Any other status means that the call has not been done
     * or has been rescheduled.
     * Make sure to exclude today's date
     * (since we might have previously updated today's call to 'reached' or 'not reached').
     *
     * EDIT: pangratios
     * do not check called_date since its always null
     * check for status 'reached' or 'not reached' and scheduled_date less than today
     *
     * @param $patient
     * @param $scheduled_call_id
     *
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     */
    public function getPreviousCall($patient, $scheduled_call_id = null)
    {
        //be careful not to consider call just made,
        //since algo already updates it before getting here.
        //check for day != today

        $call = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', SchedulerService::CALL_TYPE);
        })
            ->where('inbound_cpm_id', $patient->id)
            ->whereIn('status', ['reached', 'not reached'])
            ->whereNotNull('called_date')
            ->where('called_date', '<', Carbon::today()->startOfDay()->toDateTimeString())
            ->orderBy('called_date', 'desc')
            ->first();

        /*
        $call = Call
            ::where('inbound_cpm_id', $patient->id)
            ->where('status', '!=', 'scheduled')
            ->where('called_date', '!=', '')
//            ->where('id', '!=', $scheduled_call_id)
            ->orderBy('called_date', 'desc')
            ->first();
        */

        return $call;
    }

    //Get scheduled call
    public function getScheduledCallForPatient($patient)
    {
        $call = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', SchedulerService::CALL_TYPE);
        })
            ->where(function ($q) use (
                        $patient
                    ) {
                        $q->where('outbound_cpm_id', $patient->id)
                            ->orWhere('inbound_cpm_id', $patient->id);
                    })
            ->where('status', '=', 'scheduled')
            ->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'))
            ->first();

        return $call;
    }

    /**
     * Get today's call.
     * First try to get today's 'scheduled' call.
     * If call has already been rescheduled, it will have a 'rescheduled/cancelled' status.
     * So, run the query again to find any call of today with status of not 'reached' or 'not reached'.
     *
     * @param $patientId
     */
    public function getTodaysCall($patientId): ?Call
    {
        $query = Call::where(function ($q) {
            $q->whereNull('type')
                ->orWhere('type', '=', SchedulerService::CALL_TYPE);
        })
            ->where('inbound_cpm_id', $patientId)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
            ->orderBy('updated_at', 'desc');

        if (0 == $query->count()) {
            $query = Call::where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', SchedulerService::CALL_TYPE);
            })
                ->where('inbound_cpm_id', $patientId)
                ->whereNotIn('status', ['reached', 'not reached'])
                ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
                ->orderBy('updated_at', 'desc');
        }

        return $query->first();
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
                'type'    => SchedulerService::CALL_TYPE,
                'service' => 'phone',
                'status'  => 'scheduled',

                'inbound_phone_number' => $patient->getPhone()
                    ? $patient->getPhone()
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

    public function storeScheduledCall(
        $patientId,
        $window_start,
        $window_end,
        $date,
        $scheduler,
        $nurse_id = null,
        $attempt_note = '',
        $is_manual = false
    ) {
        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end   = Carbon::parse($window_end)->format('H:i');

        $nurse_id = ('' == $nurse_id)
            ? null
            : $nurse_id;

        if ( ! ($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        return Call::create([
            'type'    => SchedulerService::CALL_TYPE,
            'service' => 'phone',
            'status'  => 'scheduled',

            'attempt_note' => $attempt_note,

            'scheduler' => $scheduler,
            'is_manual' => $is_manual,

            'inbound_phone_number' => $patient->patientInfo->phone
                ? $patient->patientInfo->phone
                : '',

            'outbound_phone_number' => '',

            'inbound_cpm_id'  => $patient->id,
            'outbound_cpm_id' => $nurse_id,

            'call_time'  => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            //make sure we are sending the date correctly formatted
            'scheduled_date' => $date->format('Y-m-d'),
            'window_start'   => $window_start,
            'window_end'     => $window_end,

            'is_cpm_outbound' => true,
        ]);
    }

    /**
     * Assuming that the scheduler service has scheduled calls for all patients:
     * 1. Sync their calls so they are at same time.
     * 2. If there is a manual call, it should be skipped and not taken into account.
     * 3. If all are manual, nothing should be done.
     *
     * @return array
     */
    public function syncFamilialCalls()
    {
        $nurseIds = User::select('id')
            ->whereHas('roles', function ($q) {
                            $q->where('name', '=', 'care-center');
                        })
            ->pluck('id')
            ->all();

        $families    = Family::all();
        $familyCalls = [];

        foreach ($families as $family) {
            //First get family members
            $patients = $family->patients()->get();

            //Then get their family's calls
            $scheduledCalls          = [];
            $scheduledCallsScheduler = collect();

            $designatedNurse = null;

            $window_start = '09:00:00';
            $window_end   = '17:00:00';

            $familyUsers = [];

            foreach ($patients as $patient) {
                $familyUsers[$patient->user_id] = $patient->user;

                $call = $this->getScheduledCallForPatient($familyUsers[$patient->user_id]);

                if (is_a($call, Call::class)) {
                    //If the patient has a call and is not manual,
                    if ( ! $call->is_manual) {
                        $window_start = $call->window_start;
                        $window_end   = $call->window_end;

                        $date = Carbon::parse($call->scheduled_date);
                        $date->setTimeFromTimeString(Carbon::parse($call->window_start)->toTimeString());

                        //Set one of the nurses as the designated nurse
                        $designatedNurse = $call->outbound_cpm_id;

                        //get calls that are in the future
                        if ($date->isFuture()) {
                            $scheduledCallsScheduler->push([
                                'scheduler' => $call->scheduler,
                                'date'      => $date->toDateTimeString(),
                            ]);
                        }
                    }
                } else {
                    //fill in some call info:
                    $call = Call::create([
                        'type'    => SchedulerService::CALL_TYPE,
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

            if ($scheduledCallsScheduler->isNotEmpty()) {
                //determine minimum date, but also check if there are calls scheduled from nurses
                $scheduledCallsCollect = $scheduledCallsScheduler
                    ->whereIn('scheduler', $nurseIds);

                if ($scheduledCallsCollect->count() > 0) {
                    $candidateDates = $scheduledCallsCollect->pluck('date')->all();
                    $minDate        = Carbon::parse(min($candidateDates));
                } else {
                    $candidateDates = $scheduledCallsScheduler->pluck('date')->all();
                    $minDate        = Carbon::parse(min($candidateDates));
                }

                //patientId => patientScheduledCall
                foreach ($scheduledCalls as $key => $value) {
                    //this is a manual call, do not touch
                    if ($value->is_manual) {
                        continue;
                    }

                    $callPatient = $familyUsers[$key];

                    $value->scheduled_date       = $minDate->toDateTimeString();
                    $value->inbound_phone_number = $callPatient->getPhone()
                        ? $callPatient->getPhone()
                        : '';
                    $value->outbound_cpm_id = $designatedNurse;
                    $value->window_start    = $window_start;
                    $value->window_end      = $window_end;
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
            if (null != $last_note_time && null != $last_activity_time) {
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
                        \Log::info("Patient Info Id {$patient->id}");
                        continue;
                    }

                    //make sure we have a call attempt and a scheduled call.
                    if (is_object($scheduled_call) && is_object($last_attempted_call)) {
                        $status = ('reached' == $last_attempted_call->status)
                            ? true
                            : false;

                        $last_attempted_time = $last_attempted_call->called_date;

                        if ($status) {
                            $data = (new SuccessfulHandler(
                                $patient,
                                Carbon::parse($last_attempted_time),
                                $last_attempted_call
                            ));
                        } else {
                            $data = (new UnsuccessfulHandler(
                                $patient,
                                Carbon::parse($last_attempted_time),
                                $last_attempted_call
                            ));
                        }

                        $scheduled_call->scheduler      = 'refresher algorithm';
                        $scheduled_call->scheduled_date = $data['date'];
                        $scheduled_call->save();

                        $reprocess_bucket[] = 'Patient: '.$patient->user_id.' was tuned, will now be called on '.$scheduled_call->scheduled_date;
                    }
                }
            }
        }

        return empty($reprocess_bucket)
            ? 'No Patients Need Refreshin\'!'
            : $reprocess_bucket;
    }

    /**
     * Update a call based on info received from note.
     * If a call does not exist, one is created and linked to this note.
     *
     * @param $call
     * @param $status
     * @param mixed $attestedProblems
     */
    public function updateCallWithNote(
        Note $note,
        $call,
        $status,
        $attestedProblems = null
    ) {
        $patient = $note->patient;

        if ($call) {
            $call->status          = $status;
            $call->note_id         = $note->id;
            $call->called_date     = Carbon::now()->toDateTimeString();
            $call->outbound_cpm_id = Auth::user()->id;
            $call->save();
        } else {
            // If call doesn't exist, make one and store it
            $call = $this->noteService->storeCallForNote(
                $note,
                $status,
                $patient,
                Auth::user(),
                'outbound',
                'core algorithm'
            );
        }

        if ($attestedProblems) {
            $call->attachAttestedProblems($attestedProblems);
        }
    }

    /**
     * Success is the call's status
     * (true for 'reached', false for 'not reached').
     *
     * Update today's call based on note.
     * Check if a next call is already scheduled.
     * If a call is scheduled return null.
     * If no call is scheduled return a prediction.
     *
     * @param $patient
     * @param $noteId
     * @param $callStatus - 'reached', 'not reached', 'ignored'
     * @param mixed $attestedProblems
     *
     * @return array
     */
    public function updateTodaysCallAndPredictNext(
        $patient,
        $noteId,
        $callStatus,
        $attestedProblems = null
    ) {
        $scheduled_call = $this->getTodaysCall($patient->id);

        $note = Note::find($noteId);

        $this->updateCallWithNote(
            $note,
            $scheduled_call,
            $callStatus,
            $attestedProblems
        );

        if (Call::IGNORED != $callStatus) {
            $isCallBack = null != $scheduled_call && SchedulerService::CALL_BACK_TYPE === $scheduled_call->sub_type;
            $this->patientWriteRepository->updateCallLogs(
                $patient->patientInfo,
                Call::REACHED == $callStatus,
                $isCallBack
            );
        }

        $nextCall = SchedulerService::getNextScheduledCall($patient->id, true);
        if ($nextCall) {
            return null;
        }

        $previousCall = $this->getPreviousCall($patient, $scheduled_call['id']);

        if (Call::REACHED == $callStatus) {
            $prediction = (new SuccessfulHandler(
                $patient->patientInfo,
                Carbon::now(),
                $previousCall
            ))->handle();
        } else {
            $prediction = (new UnsuccessfulHandler(
                $patient->patientInfo,
                Carbon::now(),
                $previousCall
            ))->handle();
        }

        $prediction['successful'] = Call::REACHED == $callStatus;

        return $prediction;
    }
}
