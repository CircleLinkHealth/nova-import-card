<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Calls;

use App\Algorithms\Calls\NurseFinder;
use App\Algorithms\Calls\SuccessfulHandler;
use App\Algorithms\Calls\UnsuccessfulHandler;
use App\Call;
use App\Events\CallIsReadyForAttestedProblemsAttachment;
use App\Note;
use App\Services\NoteService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\Family;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Support\Facades\Auth;

class SchedulerService
{
    const CALL_BACK_TYPE                              = 'Call Back';
    const CALL_TYPE                                   = 'call';
    const PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE = 'Provider Request For Care Plan Approval';
    const SCHEDULE_NEXT_CALL_PER_PATIENT_SMS          = 'Schedule Next Call per patient\'s SMS';
    const TASK_TYPE                                   = 'task';

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

    public function ensurePatientHasScheduledCall(User $patient, string $scheduler = null)
    {
        $call = $this->getScheduledCallForPatient($patient);
        if ($call) {
            return;
        }

        $now = Carbon::now();

        // Always load the carePlan because Observers don't work with update queries,
        // and we want to make sure we are using the most up-to-date CP.
        $patient->load(['patientInfo', 'carePlan']);

        if ( ! $this->shouldScheduleCall($patient)) {
            return;
        }

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

        if ( ! $scheduler) {
            $scheduler = 'call checker algorithm';
        }
        $this->storeScheduledCall(
            $patient,
            $next_predicted_contact_window['window_start'],
            $next_predicted_contact_window['window_end'],
            $next_predicted_contact_window['day'],
            $scheduler,
            $nurseId,
            '',
            false
        );
    }

    public static function getAsapTaskSince($patientId, $taskName, Carbon $since = null)
    {
        if ( ! $since) {
            $since = now();
        }

        return Call::where('inbound_cpm_id', $patientId)
            ->where('type', '=', 'task')
            ->where('sub_type', '=', $taskName)
            ->where('asap', '=', 1)
            ->where('scheduled_date', '>=', $since->toDateString())
            ->orderBy('scheduled_date', 'desc')
            ->first();
    }

    public static function getLastUnsuccessfulCall($patientId, $calledDate = null, $withParticipants = true): ?Call
    {
        if ( ! $calledDate) {
            $calledDate = now();
        }

        return Call::when($withParticipants, function ($q) {
            return $q->with([
                'inboundUser' => function ($q) {
                    $q->without(['roles', 'perms'])
                        ->with([
                            'billingProvider' => function ($q) {
                                $q->with([
                                    'user' => function ($q) {
                                        $q->without(['roles', 'perms'])
                                            ->select(['id', 'last_name']);
                                    },
                                ]);
                            },
                            'primaryPractice' => function ($q) {
                                $q->select(['id', 'display_name']);
                            },
                        ]);
                },
                'outboundUser' => function ($q) {
                    $q->without(['roles', 'perms'])
                        ->select(['id', 'first_name']);
                },
            ]);
        })
            ->whereInboundCpmId($patientId)
            ->where('status', '=', Call::NOT_REACHED)
            ->whereBetween('called_date', [$calledDate->copy()->startOfDay(), $calledDate->copy()->endOfDay()])
            ->first();
    }

    public static function getNextScheduledActivities($patientId, $excludeToday)
    {
        return Call::where('inbound_cpm_id', $patientId)
            ->where('status', '=', Call::SCHEDULED)
            ->when(
                $excludeToday,
                function ($query) {
                    $query->where('scheduled_date', '>', Carbon::today()->format('Y-m-d'));
                },
                function ($query) {
                    $query->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'));
                }
            )
            ->orderBy('scheduled_date', 'desc');
    }

    public static function getNextScheduledActivity($patientId, $subType, $excludeToday = false)
    {
        return self::getNextScheduledActivities($patientId, $excludeToday)
            ->where('type', '=', 'task')
            ->where('sub_type', '=', $subType)
            ->first();
    }

    public static function getNextScheduledCall($patientId, $excludeToday = false): ?Call
    {
        return self::getNextScheduledActivities($patientId, $excludeToday)
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', SchedulerService::CALL_TYPE);
            })
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

        $call = Call::where(
            function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', SchedulerService::CALL_TYPE);
            }
        )
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
        return $this->scheduledCallQuery($patient)
            ->first();
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
        $query = Call::where(
            function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', SchedulerService::CALL_TYPE);
            }
        )
            ->where('inbound_cpm_id', $patientId)
            ->where('status', 'scheduled')
            ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
            ->orderBy('updated_at', 'desc');

        if (0 == $query->count()) {
            $query = Call::where(
                function ($q) {
                    $q->whereNull('type')
                        ->orWhere('type', '=', SchedulerService::CALL_TYPE);
                }
            )
                ->where('inbound_cpm_id', $patientId)
                ->whereNotIn('status', ['reached', 'not reached'])
                ->where('scheduled_date', '=', Carbon::today()->format('Y-m-d'))
                ->orderBy('updated_at', 'desc');
        }

        return $query->first();
    }

    public function hasScheduledCall(User $patient)
    {
        return $this->scheduledCallQuery($patient)->exists();
    }

    public function importCallsFromCsv($csv)
    {
        $failed = [];
        foreach ($csv as $row) {
            $patient = User::where('first_name', $row['Patient First Name'])
                ->where('last_name', $row['Patient Last Name'])
                ->whereHas(
                    'patientInfo',
                    function ($q) use (
                                   $row
                               ) {
                        $q->where(
                            'birth_date',
                            Carbon::parse($row['DOB'])->toDateString()
                        );
                    }
                )
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

            Call::updateOrCreate(
                [
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
                ],
                [
                    'scheduled_date' => Carbon::parse($row['Next call date'])->toDateString(),

                    'window_start' => empty($fromTime)
                        ? '09:00'
                        : Carbon::parse($fromTime)->format('H:i'),

                    'window_end' => empty($toTime)
                        ? '17:00'
                        : Carbon::parse($toTime)->format('H:i'),
                ]
            );

            $calls[] = $call;
        }

        return $failed;
    }

    public function removeScheduledCallsForWithdrawnAndPausedPatients(array $patientUserIds = [])
    {
        $cb = function ($query) use (
            $patientUserIds
        ) {
            $query->select('user_id')->from('patient_info')->whereIn(
                'ccm_status',
                [
                    Patient::WITHDRAWN_1ST_CALL,
                    Patient::WITHDRAWN,
                    Patient::PAUSED,
                    Patient::UNREACHABLE,
                ]
            )->when(
                ! empty($patientUserIds),
                function ($q) use (
                    $patientUserIds
                ) {
                    $q->whereIn('user_id', $patientUserIds);
                }
            );
        };

        return Call::where(
            function ($q) use (
                $cb
            ) {
                $q->whereIn('outbound_cpm_id', $cb)
                    ->orWhereIn('inbound_cpm_id', $cb);
            }
        )->whereHas('inboundUser', function ($q) {
            return $q->ofType('participant');
        })
            ->where('status', '=', 'scheduled')
            ->delete();
    }

    /**
     * @param $taskNote
     * @param $scheduler
     * @param null $phoneNumber
     *
     * @throws \Exception
     */
    public function scheduleAsapCallbackTask(User $patient, $taskNote, $scheduler, $phoneNumber = null): Call
    {
        // check if there is already a task scheduled
        /** @var Call $existing */
        $existing = Call::where('type', '=', SchedulerService::TASK_TYPE)
            ->where('sub_type', '=', SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS)
            ->where('status', '=', Call::SCHEDULED)
            ->where('inbound_cpm_id', '=', $patient->id)
            ->first();

        if ($existing) {
            $existing->attempt_note = "{$existing->attempt_note}\n{$taskNote}";
            $existing->asap         = true;
            $existing->save();

            return $existing;
        }

        $scheduledDate = (new PatientContactWindow())->getEarliestWindowForPatientFromDate(
            $patient->patientInfo,
            now()
        );

        $previousCall = Call::where('inbound_cpm_id', '=', $patient->id)
            ->orderBy('scheduled_date', 'desc')
            ->first();

        $nurseId     = null;
        $nurseFinder = (new NurseFinder($patient->patientInfo, null, null, null, $previousCall))->find();
        if ($nurseFinder && isset($nurseFinder['nurse'])) {
            $nurseId = $nurseFinder['nurse'];
        }
        if ( ! $nurseId) {
            $nurseId = StandByNurseUser::id();
        }

        if ( ! $nurseId) {
            throw new \Exception("could not find nurse for patient[$patient->id]");
        }

        $nowString = now()->toDateTimeString();

        return Call::create(
            [
                'type'                  => SchedulerService::TASK_TYPE,
                'sub_type'              => SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS,
                'status'                => Call::SCHEDULED,
                'attempt_note'          => "Email/SMS Response at $nowString: $taskNote",
                'scheduler'             => $scheduler,
                'is_manual'             => false,
                'inbound_phone_number'  => $phoneNumber ?? '',
                'outbound_phone_number' => '',
                'inbound_cpm_id'        => $patient->id,
                'outbound_cpm_id'       => $nurseId,
                'call_time'             => 0,
                'asap'                  => true,
                'is_cpm_outbound'       => true,
                'scheduled_date'        => $scheduledDate['day'],
            ]
        );
    }

    /**
     * @param $phoneNumber
     * @param $taskNote
     * @param $scheduler
     *
     * @throws \Exception
     *
     * @return Call|\Illuminate\Database\Eloquent\Model
     */
    public function scheduleAsapCallbackTaskFromSms(User $patient, $phoneNumber, $taskNote, $scheduler)
    {
        return $this->scheduleAsapCallbackTask($patient, $taskNote, $scheduler, $phoneNumber);
    }

    public function scheduledCallQuery(User $patient)
    {
        return Call::where(
            function ($q) {
                $q->whereNull('type')
                    ->orWhere('type', '=', SchedulerService::CALL_TYPE);
            }
        )
            ->where(
                function ($q) use (
                    $patient
                ) {
                    $q->where('outbound_cpm_id', $patient->id)
                        ->orWhere('inbound_cpm_id', $patient->id);
                }
            )
            ->where('status', '=', Call::SCHEDULED)
            ->where('scheduled_date', '>=', Carbon::today()->format('Y-m-d'));
    }

    /**
     * @param Patient $patient
     * @param $oldValue
     * @param $newValue
     */
    public function shouldScheduleCall(User $patient): bool
    {
        if (Patient::ENROLLED != $patient->patientInfo->ccm_status) {
            return false;
        }

        if ( ! $patient->carePlan) {
            return false;
        }

        if ($patient->carePlan->isClhAdminApproved()) {
            return true;
        }

        if ($patient->carePlan->isProviderApproved()) {
            return true;
        }

        if ($patient->carePlan->isRnApproved()) {
            return true;
        }

        return false;
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
        if ($patientId instanceof User) {
            $patient = $patientId;
            $patient->loadMissing('patientInfo');
        } else {
            $patient = User::with('patientInfo')->without(['roles', 'perms'])->find($patientId);
        }

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end   = Carbon::parse($window_end)->format('H:i');

        $nurse_id = ! is_numeric($nurse_id) ? null : $nurse_id;

        if ( ! ($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        return Call::create(
            [
                'type'    => SchedulerService::CALL_TYPE,
                'service' => 'phone',
                'status'  => 'scheduled',

                'attempt_note' => $attempt_note,

                'scheduler' => $scheduler,
                'is_manual' => $is_manual,

                'inbound_phone_number' => $patient->patientInfo->phone ?? null,

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
            ]
        );
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
            ->whereHas(
                'roles',
                function ($q) {
                    $q->where('name', '=', 'care-center');
                }
            )
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
                            $scheduledCallsScheduler->push(
                                [
                                    'scheduler' => $call->scheduler,
                                    'date'      => $date->toDateTimeString(),
                                ]
                            );
                        }
                    }
                } else {
                    //fill in some call info:
                    $call = Call::create(
                        [
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
                        ]
                    );
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
    public function updateOrCreateCallWithNote(
        Note $note,
        ?Call $call,
        $status,
        $attestedProblems = null
    ) {
        if ($call) {
            $call->status          = $status;
            $call->note_id         = $note->id;
            $call->called_date     = Carbon::now()->toDateTimeString();
            $call->outbound_cpm_id = Auth::user()->id;
            $call->save();
        } else {
            $patient = $note->patient;
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

        //If this is the first call being created for the month, the patient might not have a summary - which gets created on the 'saved' event of the call
        //So we are dispatching an event with a delayed job to make sure that the summary will be created before we attach the problems
        if ($attestedProblems) {
            event(new CallIsReadyForAttestedProblemsAttachment($call, $attestedProblems));
        }

        return $call;
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
        $scheduled_call = $this->updateOrCreateCallWithNote(
            $note = Note::find($noteId),
            $scheduled_call = $this->getTodaysCall($patient->id),
            $callStatus,
            $attestedProblems
        );

        $this->patientWriteRepository->updateCallLogs(
            $patient->patientInfo,
            Call::REACHED == $callStatus,
            ! is_null($scheduled_call) && SchedulerService::CALL_BACK_TYPE === $scheduled_call->sub_type
        );

        if (SchedulerService::getNextScheduledCall($patient->id, true)) {
            return null;
        }

        $previousCall = $this->getPreviousCall($patient, optional($scheduled_call)->id);

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
