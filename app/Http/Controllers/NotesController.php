<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Algorithms\Calls\NextCallSuggestor\Handlers\SuccessfulCall;
use App\Algorithms\Calls\NextCallSuggestor\Handlers\UnsuccessfulCall;
use CircleLinkHealth\SharedModels\Entities\Call;
use App\Contracts\ReportFormatter;
use App\Events\CarePlanWasApproved;
use App\Events\NoteFinalSaved;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Http\Requests\CreateNoteRequest;
use App\Http\Requests\NotesReport;
use CircleLinkHealth\Customer\Http\Requests\SafeRequest;
use App\Jobs\SendSingleNotification;
use CircleLinkHealth\SharedModels\Entities\Note;
use App\Rules\PatientEmailAttachments;
use App\Rules\PatientEmailDoesNotContainPhi;
use App\Rules\ValidatePatientCustomEmail;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use App\Services\CPM\CpmMedicationService;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use CircleLinkHealth\Customer\Services\NoteService;
use App\Services\PatientCustomEmail;
use App\ValueObjects\CreateManualCallAfterNote;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\PatientWriteRepository;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporter;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;
use Validator;

class NotesController extends Controller
{
    private $formatter;
    private $patientRepo;
    private $service;

    public function __construct(
        NoteService $noteService,
        ReportFormatter $formatter,
        PatientWriteRepository $patientWriteRepository
    ) {
        $this->service     = $noteService;
        $this->formatter   = $formatter;
        $this->patientRepo = $patientWriteRepository;
    }

    public function create(
        CreateNoteRequest $request,
        $patientId,
        $noteId = null,
        CpmMedicationService $medicationService
    ) {
        if ( ! $patientId) {
            return response('Missing param: patientId', 401);
        }

        // patient view
        /** @var User $patient */
        $patient = User::with([
            'carePlan' => function ($q) {
                return $q->select(['id', 'user_id', 'status', 'created_at']);
            },
            'patientSummaries' => function ($q) {
                return $q->where('month_year', Carbon::now()->startOfMonth());
            },
            'primaryPractice' => function ($q) {
                return $q->with(['settings']);
            },
            'patientInfo' => function ($q) {
                return $q->with(['contactWindows']);
            },
            'careTeamMembers' => function ($q) {
                return $q->with([
                    'user' => function ($q) {
                        return $q->select(['id', 'first_name', 'last_name', 'suffix']);
                    },
                ])->has('user');
            },
        ])->find($patientId);
        if ( ! $patient) {
            return response('User not found', 401);
        }

        $patient_name = $patient->getFullName();

        $careteam_info = $patient
            ->careTeamMembers
            ->mapWithKeys(function (CarePerson $member) {
                return [$member->member_user_id => optional($member->user)->getFullName() ?? 'N/A'];
            })
            ->toArray();
        asort($careteam_info);

        $userTimeZone = empty($patient->timezone)
            ? 'America/New_York'
            : $patient->timezone;

        if (empty($patient->program_id)) {
            return response("User's Program not found", 401);
        }

        $author      = Auth::user();
        $author_id   = $author->id;
        $author_name = $author->getFullName();

        //Patient Call Windows:
        //set contact flag
        $patient_contact_window_exists = $patient->patientInfo->contactWindows->isNotEmpty();
        //this will create a new one if there are no contactWindows
        $window = PatientContactWindow::getPreferred($patient->patientInfo);

        $contact_days_array = [];
        if (is_object($patient->patientInfo->contactWindows)) {
            $contact_days_array = array_merge(explode(',', $patient->patientInfo->preferred_cc_contact_days));
        }

        $existingNote = null;
        $existingCall = null;
        if ($noteId) {
            /** @var Note $existingNote */
            $existingNote = Note::with(['call'])->findOrFail($noteId);
            $existingCall = $existingNote->call;
        } else {
            $existingNote = Note::where('patient_id', '=', $patientId)
                ->where('author_id', '=', $author_id)
                ->where('status', '=', Note::STATUS_DRAFT)
                ->first();
        }

        if ($existingNote && $existingNote->author_id !== $author_id) {
            return response('You can only edit notes created by you.', 403);
        }

        if ($existingNote && Note::STATUS_COMPLETE === $existingNote->status) {
            return response('You can only edit DRAFT notes.', 401);
        }

        $nurse_patient_tasks = Call::where('status', '=', 'scheduled')
            ->where('type', '=', 'task')
            ->where('inbound_cpm_id', '=', $patientId)
            ->where('outbound_cpm_id', '=', $author_id)
            ->select(
                [
                    'id',
                    'type',
                    'sub_type',
                    'attempt_note',
                    'scheduled_date',
                    'window_start',
                    'window_end',
                ]
            )
            ->get();

        // we used to send $meds for Phoenix Heart
        // removed server side implementation because it could use optimization
        $meds    = [];
        $reasons = [
            'No Longer Interested',
            'Moving out of Area',
            'New Physician',
            'Cost / Co-Pay',
            'Changed Insurance',
            'Dialysis / End-Stage Renal Disease',
            'Expired',
            'Patient in Hospice',
            'Other',
        ];

        $withdrawnReasons       = array_combine($reasons, $reasons);
        $patientWithdrawnReason = $patient->getWithdrawnReason();

        $performedAt = optional($existingNote)->performed_at ?? Carbon::now();

        $hasSuccessfulCall = ! empty($patient->patientInfo->last_successful_contact_time);

        $answerFromMoreInfo = '';
        if ( ! $hasSuccessfulCall) {
            // this should have been in a separate ajax call
            $answerFromMoreInfo = $this->getAnswerFromSelfEnrolmentSurvey($patientId);
        }

        // RN Approval Flow - session must have SESSION_RN_APPROVED_KEY
        $shouldRnApprove = optional($patient->carePlan)->shouldRnApprove($author);
        $hasRnApprovedCp = false;
        if ($shouldRnApprove) {
            $approvedId      = $request->session()->get(ProviderController::SESSION_RN_APPROVED_KEY, null);
            $hasRnApprovedCp = $approvedId && $approvedId == $author->id;
        }

        $view_data = [
            'userTime'                        => $performedAt->setTimezone($patient->timezone)->format('Y-m-d\TH:i'),
            'program_id'                      => $patient->program_id,
            'patient'                         => $patient,
            'patient_name'                    => $patient_name,
            'note_types'                      => Activity::input_activity_types(),
            'task_types_to_topics'            => Activity::task_types_to_topics(),
            'tasks'                           => $nurse_patient_tasks,
            'author_id'                       => $author_id,
            'author_name'                     => $author_name,
            'careteam_info'                   => $careteam_info,
            'userTimeZone'                    => $userTimeZone,
            'window'                          => $window,
            'window_flag'                     => $patient_contact_window_exists,
            'contact_days_array'              => $contact_days_array,
            'notifies_text'                   => $patient->getNotifiesText(),
            'note_channels_text'              => $patient->getNoteChannelsText(),
            'medications'                     => $meds,
            'withdrawnReasons'                => $withdrawnReasons,
            'patientWithdrawnReason'          => $patientWithdrawnReason,
            'note'                            => $existingNote,
            'call'                            => $existingCall,
            'cpmProblems'                     => (new CpmProblemService())->all(),
            'patientRequestToKnow'            => $answerFromMoreInfo,
            'hasSuccessfulCall'               => $hasSuccessfulCall,
            'attestationRequirements'         => $this->getAttestationRequirementsIfYouShould($patient),
            'shouldShowForwardNoteSummaryBox' => is_null($existingNote) || 'draft' === optional($existingNote)->status,
            'shouldRnApprove'                 => $shouldRnApprove,
            'hasRnApprovedCarePlan'           => $hasRnApprovedCp,
        ];

        return view('wpUsers.patient.note.create', $view_data);
    }

    public function deleteDraft(Request $request, $patientId, $noteId)
    {
        $note = Note::findOrFail($noteId);
        if (Note::STATUS_DRAFT !== $note->status) {
            throw new \Exception('You cannot delete a non-draft note');
        }

        if ($note->author_id != auth()->id()) {
            throw new \Exception('Only the author of the note can delete it');
        }

        $note->delete();

        return redirect()->route('patient.note.index', ['patientId' => $patientId]);
    }

    public function download(Request $request, $patientId, $noteId)
    {
        $format = $request->input('format');
        $note   = Note::with('patient.ccdProblems')->wherePatientId($patientId)->findOrFail($noteId);

        if ('pdf' === $format) {
            return response()->download($note->toPdf(), "patient-$patientId-note-$noteId.pdf")->deleteFileAfterSend();
        }
        if ('html' === $format) {
            return $note->toPdf(null, true);
        }

        return redirect()->back();
    }

    /**
     * @param $noteId
     *
     * @return Collection|Model|Note|Note[]|null
     */
    public function getNoteForAddendum($noteId)
    {
        return Note::findOrFail($noteId);
    }

    public function index(
        Request $request,
        $patientId,
        $showAll = false
    ) {
        $date = Carbon::now()->subMonth(2);
        if (true == $showAll) {
            //earliest day possible
            //works with both mysql and pgsql
            $date = '1900-01-01';
        }

        $patient = User::with(
            [
                'activities' => function ($q) use ($date) {
                    $q->where('logged_from', '=', 'manual_input')
                        ->where('performed_at', '>=', $date)
                        ->with('meta')
                        ->groupBy(DB::raw('provider_id, DATE(performed_at),type, lv_activities.id'))
                        ->orderBy('performed_at', 'desc');
                },
                'appointments' => function ($q) use ($date) {
                    $q->where('date', '>=', $date)
                        ->orderBy('date', 'desc');
                },
                'billingProvider',
                'primaryPractice',
                'notes' => function ($q) use ($date) {
                    $q->where('performed_at', '>=', $date)
                        ->with(['author', 'call', 'notifications'])
                        ->orderBy('performed_at', 'desc');
                },
                'patientInfo',
            ]
        )
            ->findOrFail($patientId);

        //if a patient has no notes for the past 2 months, we load all the results and DON'T display 'show all notes button'
        if ($patient->notes->isEmpty() and false == $showAll) {
            $patient->load(
                [
                    'notes' => function ($notes) {
                        $notes->with(['author', 'call', 'notifications']);
                    },
                ]
            );

            $showAll = null;
        }

        $messages = \Session::get('messages');

        $report_data = $this->formatter->formatDataForNotesAndOfflineActivitiesReport($patient);

        return view(
            'wpUsers.patient.note.index',
            [
                'activity_json' => $report_data,
                'patient'       => $patient,
                'messages'      => $messages,
                'showAll'       => $showAll,
            ]
        );
    }

    public function listing(NotesReport $request)
    {
        /** @var User $session_user */
        $session_user = auth()->user();

        if ( ! should_show_notes_report($session_user->program_id)) {
            return redirect()->back();
        }

        if ($request->has('getNotesFor')) {
            $providers = $this->getProviders($request->getNotesFor);
        }

        $data['providers'] = User::SCOPE_LOCATION === $session_user->scope
            ? collect([$session_user->id => $session_user->display_name])
            : User::whereIn('id', $session_user->viewableProviderIds())
                ->pluck('display_name', 'id')
                ->sort();

        $data['practices'] = User::SCOPE_LOCATION === $session_user->scope
            ? collect()
            : Practice::whereIn('id', $session_user->viewableProgramIds())
                ->pluck('display_name', 'id')
                ->sort();

        $start = Carbon::now()->startOfMonth()->subMonth(
            $request->has('range')
                ? $request->range
                : 0
        )->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        //Check to see whether there are providers to fetch notes for.
        if (isset($providers) && ! empty($providers)) {
            if ($request->has('mail_filter')) {
                $notes = $this->service->getForwardedNotesWithRangeForProvider($providers, $start, $end);
            } else {
                $notes = $this->service->getNotesWithRangeForProvider($providers, $start, $end);
            }
            if ( ! empty($notes)) {
                $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
            }
            $data['notes']              = $notes;
            $data['isProviderSelected'] = true;
        } else {
            if ($session_user->hasRole(['administrator', 'care-center']) && $request->has('admin_filter')) {
                //If an admin is viewing this, we show them all
                //notes from all providers who are in the
                //same program as the provider selected.
                $notes = $this->service->getAllForwardedNotesWithRange(Carbon::parse($start), Carbon::parse($end));
                if ( ! empty($notes)) {
                    $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
                }
                $data['notes']              = $notes;
                $data['isProviderSelected'] = true;
            } else {
                // Not enough data for a report, return only the essentials
                $data['notes']              = false;
                $data['isProviderSelected'] = false;
            }
        }

        return view('wpUsers.patient.note.list', $data)->with('input', $request->input());
    }

    public function send(
        SafeRequest $request,
        $patientId,
        $noteId
    ) {
        $input                  = $request->allSafe();
        $shouldSendPatientEmail = isset($input['email-patient']);
        $shouldNotifyCareTeam   = isset($input['notify_careteam']);
        $shouldNotifySupport    = isset($input['notify_circlelink_support']);

        $note = Note::where('patient_id', $input['patient_id'])
            ->findOrFail($input['noteId']);

        $patient = User::findOrFail($patientId);

        $note->forward($shouldNotifyCareTeam, $shouldNotifySupport);
        if ($shouldSendPatientEmail) {
            Validator::make($input, [
                'patient-email-body' => ['sometimes', new PatientEmailDoesNotContainPhi($patient)],
                'attachments'        => ['sometimes', new PatientEmailAttachments()],
            ])->validate();

            $this->sendPatientEmail($input, $patient, $note);
        }

        return redirect()->route('patient.note.index', [$patientId, $noteId]);
    }

    public function show(
        $patientId,
        $noteId
    ) {
        /**
         * @var Note
         */
        $note = Note::with('author')
            ->where('id', $noteId)
            ->where('patient_id', $patientId)
            ->with([
                'call',
                'notifications',
                'patient' => function ($q) {
                    return $q->with([
                        'careTeamMembers' => function ($q) {
                            return $q->with([
                                'user' => function ($q) {
                                    return $q->select(['id', 'first_name', 'last_name', 'suffix']);
                                },
                            ]);
                        },
                    ]);
                },
            ])
            ->firstOrFail();

        /** @var User $patient */
        $patient = $note->patient;

        $this->service->markNoteAsRead(auth()->user(), $note);

        $readers = $this->service->getSeenForwards($note);

        //Set up note packet for view
        $data = [];

        //Sets up tags for patient note tags
        $meta_tags = $this->service->tags($note);

        $author = $note->author;

        $data['type']         = $note->type;
        $data['id']           = $note->id;
        $data['author_id']    = $author->id;
        $data['performed_at'] = $note->performed_at;
        $data['created_at']   = presentDate($note->created_at);
        $provider             = User::find($note->author_id);
        if ($provider) {
            $data['provider_name'] = $provider->getFullName();
        } else {
            $data['provider_name'] = '';
        }

        $data['summary']      = $note->summary;
        $data['summary_type'] = $note->summary_type;
        $data['comment']      = $note->body;
        $data['addendums']    = $note->addendums->sortByDesc('created_at');

        $careteam_info = $patient
            ->careTeamMembers
            ->mapWithKeys(function (CarePerson $member) {
                return [$member->member_user_id => optional($member->user)->getFullName() ?? 'N/A'];
            })
            ->toArray();
        asort($careteam_info);

        $view_data = [
            'note'                            => $data,
            'userTime'                        => $note->performed_at->setTimezone($patient->timezone)->format('Y-m-d\TH:i'),
            'userTimeZone'                    => $patient->timezone,
            'careteam_info'                   => $careteam_info,
            'patient'                         => $patient,
            'program_id'                      => $patient->program_id,
            'meta'                            => $meta_tags,
            'hasReaders'                      => $readers->all(),
            'notifies_text'                   => $patient->getNotifiesText(),
            'note_channels_text'              => $patient->getNoteChannelsText(),
            'author'                          => $author,
            'patientEmails'                   => $this->service->getNoteEmails($note),
            'shouldShowForwardNoteSummaryBox' => false,
        ];

        return view('wpUsers.patient.note.view', $view_data);
    }

    /**
     * Store a note.
     * If note has call and user is care-center:
     * Update TODAY's call based on the note.
     * Set TODAY's call status to 'reached' or 'not reached'.
     * If another call for the future is scheduled redirect to Notes page.
     * If no other call is scheduled redirect to Schedule Next Call Attempt page
     * with a next call prediction.
     *
     * Also: in some conditions call will be stored for other roles as well.
     * They are never redirected to Schedule Next Call page.
     *
     * @param $patientId
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        SafeRequest $request,
        SchedulerService $schedulerService,
        $patientId
    ) {
        //check if this is an existing note:
        //  - update it
        //  - do not associate with any calls (or tasks)
        //  - if current status is 'draft'
        //    - check if there is already a scheduled call for the future
        //    - if there is not, switch to Schedule Next Call page
        //    - set status to 'complete'

        //check if this is a new note:
        //  - create it
        //  - see if it should be associated with any calls (or tasks)
        //  - check if there is already a scheduled call for the future
        //  - if there is not, switch to Schedule Next Call page
        ////  - set status to 'complete'

        $input = $request->allSafe();

        /** @var User $author */
        $author = auth()->user();

        /** @var User $patient */
        $patient = User::with(
            [
                'carePlan' => function ($q) {
                    $q->select(['id', 'user_id', 'status', 'created_at']);
                },
            ]
        )->findOrFail($patientId);

        if ( ! isset($input['body']) || null === $input['body']) {
            return redirect()
                ->back()
                ->withErrors(['Cannot create note with empty body.'])
                ->withInput();
        }
        $successfulClinicalCall = isset($input['call_status']) && Call::REACHED === $input['call_status'];

        // RN Approval Flow - session must have SESSION_RN_APPROVED_KEY
        $hasRnApprovedCp = false;
        if (optional($patient->carePlan)->shouldRnApprove($author) &&
            $successfulClinicalCall) {
            $approvedId      = $request->session()->get(ProviderController::SESSION_RN_APPROVED_KEY, null);
            $hasRnApprovedCp = $approvedId && $approvedId == auth()->id();
            if ( ! $hasRnApprovedCp) {
                $errors = ['Cannot save note. Please click on Ready For Dr. in View Care Plan.'];
                try {
                    $input['status'] = 'draft';
                    $this->saveNote($patient, $input);
                } catch (\Exception $exception) {
                    $errors[] = $exception->getMessage();
                }

                return redirect()
                    ->back()
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        $attestedProblems = isset($input['attested_problems'])
            ? $input['attested_problems']
            : null;
        //attestation validation
        if ($successfulClinicalCall) {
            $this->service->validateAttestation($request, $patientId, $attestedProblems);
        }

        $shouldSendPatientEmail = isset($input['email-patient']);
        if ($shouldSendPatientEmail) {
            Validator::make($input, [
                'email-subject'        => ['sometimes', new PatientEmailDoesNotContainPhi($patient)],
                'patient-email-body'   => ['sometimes', new PatientEmailDoesNotContainPhi($patient)],
                'attachments'          => ['sometimes', new PatientEmailAttachments()],
                'custom_patient_email' => [
                    'sometimes',
                    new ValidatePatientCustomEmail(),
                ],
            ])->validate();
        }

        try {
            $input['status'] = 'complete';
            $note            = $this->saveNote($patient, $input);
        } catch (\Exception $exception) {
            return redirect()
                ->back()
                ->withErrors([$exception->getMessage()])
                ->withInput();
        }

        event(new NoteFinalSaved($note, [
            'notifyCareTeam' => $input['notify_careteam'] ?? false,
            'notifyCLH'      => $input['notify_circlelink_support'] ?? false,
            'forceNotify'    => false,
        ]));

        if ($hasRnApprovedCp) {
            event(new CarePlanWasApproved($patient, $author));
        }

        if ($shouldSendPatientEmail) {
            $this->sendPatientEmail($input, $patient, $note);
        }

        $info = $this->updatePatientInfo($patient, $input);
        $this->updatePatientCallWindows($info, $input);

        $is_phone_session = isset($input['phone']);
        $is_task          = isset($input['task_id']);

        /*
         * If task:
         *   - update task id with note
         *   - if call/call back and reached - update last_successful_contact_time
         *   - if call/call back update last contact time
         *   - do not show the schedule next call page
         *
         * If phone call AND:
         *   - if a nurse (care-center): update today's call and check if should redirect to schedule next call page
         *   - if any other role: store a call
         */

        if ($is_task) {
            $task_id     = $input['task_id'];
            $task_status = $input['task_status'];
            $call        = Call::find($task_id);
            if ($call) {
                if (Call::DONE === $task_status) {
                    if (SchedulerService::CALL_BACK_TYPE === $call->sub_type) {
                        if ( ! isset($input['call_status'])) {
                            return redirect()
                                ->back()
                                ->withErrors(["Invalid form input. Missing ['call_status']"])
                                ->withInput();
                        }

                        $schedulerService->updateOrCreateCallWithNote($note, $call, $input['call_status'], $attestedProblems);

                        $info->last_successful_contact_time = $note->performed_at->format('Y-m-d H:i:s');

                        $this->patientRepo->updateCallLogs(
                            $patient->patientInfo,
                            Call::REACHED === $call->status,
                            true,
                            $note->performed_at
                        );
                    } else {
                        $call->status = Call::DONE;
                    }
                }

                $call->note_id = $note->id;

                if ($call->isDirty()) {
                    $call->save();
                }
            }
        } else {
            if (Auth::user()->isCareCoach()) {
                $should_skip_schedule_next_call_page = in_array($info->ccm_status, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL, Patient::UNREACHABLE]);

                if ( ! $is_phone_session && $should_skip_schedule_next_call_page) {
                    return redirect()->route('patient.note.index', ['patientId' => $patientId])->with(
                        'messages',
                        [
                            'Successfully Created Note',
                        ]
                    );
                }

                if ($is_phone_session) {
                    if ( ! isset($input['call_status'])) {
                        return redirect()
                            ->back()
                            ->withErrors(["Invalid form input. Missing ['call_status']"])
                            ->withInput();
                    }

                    $call_status = $input['call_status'];
                    $is_saas     = $author->isSaas();

                    if (Call::REACHED == $call_status) {
                        //Updates when the patient was successfully contacted last
                        $info->last_successful_contact_time = $note->performed_at->format('Y-m-d H:i:s');
                    }

                    $info->save();

                    if ($should_skip_schedule_next_call_page || $is_saas) {
                        return redirect()->route('patient.note.index', ['patientId' => $patientId])->with(
                            'messages',
                            ['Successfully Created Note']
                        );
                    }

                    $schedulerService->updateTodaysCall(
                        $patient,
                        $note,
                        $call_status,
                        $attestedProblems
                    );

                    if (SchedulerService::getNextScheduledCall($patient->id, true)) {
                        return redirect()->route('patient.note.index', ['patientId' => $patientId])->with(
                            'messages',
                            ['Successfully Created Note']
                        );
                    }
                    \Session::flash(ManualCallController::SESSION_KEY, new CreateManualCallAfterNote($patient, Call::REACHED === $call_status ? new SuccessfulCall() : new UnsuccessfulCall()));

                    return redirect()->route('manual.call.create', ['patientId' => $patientId])->with(
                        'messages',
                        ['Successfully Created Note']
                    );
                }
            }

            //If successful phone call and provider, also mark as the last successful day contacted. [ticket: 592]
            if ($is_phone_session) {
                if (isset($input['call_status']) && Call::REACHED == $input['call_status']) {
                    if ($author->isProvider()) {
                        $this->service->storeCallForNote(
                            $note,
                            Call::REACHED,
                            $patient,
                            $author,
                            $author->id,
                            null
                        );

                        $this->patientRepo->updateCallLogs($patient->patientInfo, true, false, $note->performed_at);

                        $info->last_successful_contact_time = Carbon::now()->format('Y-m-d H:i:s');
                        $info->save();
                    }
                }

                if ($author->hasRole('no-ccm-care-center')) {
                    if (isset($input['welcome_call'])) {
                        $this->service->storeCallForNote(
                            $note,
                            'welcome call',
                            $patient,
                            $author,
                            $author->id,
                            null
                        );

                        $info->date_welcomed = $note->performed_at->format('Y-m-d H:i:s');
                        $info->save();
                    } else {
                        $this->service->storeCallForNote(
                            $note,
                            'welcome attempt',
                            $patient,
                            $author,
                            $author->id,
                            null
                        );
                    }

                    if (isset($input['other_call'])) {
                        $this->service->storeCallForNote(
                            $note,
                            'other call',
                            $patient,
                            $author,
                            $author->id,
                            null
                        );
                    }
                }
            }
        }

        return redirect()->route('patient.note.index', ['patientId' => $patientId])->with(
            'messages',
            [
                'Successfully Created Note',
            ]
        );
    }

    public function storeAddendum(
        Request $request,
        $patientId,
        int $noteId
    ) {
        $this->validate(
            $request,
            [
                'addendum-body' => 'required',
            ]
        );

        $getNote = $this->getNoteForAddendum($noteId);

        $note = $getNote->addendums()->create(
            [
                'body'           => $request->input('addendum-body'),
                'author_user_id' => auth()->user()->id,
            ]
        );

        return redirect()->to(
            route(
                'patient.note.view',
                ['patientId' => $patientId, 'noteId' => $noteId]
            ).'#create-addendum'
        );
    }

    public function storeDraft(
        SafeRequest $request,
        $patientId
    ) {
        //check if this is an existing note:
        //  - update it
        //  - do not associate with any calls (or tasks)
        //  - set status to draft

        //check if this is a new note:
        //  - create it
        //  - do not see if it should be associated with any calls (or tasks)
        //  - set status to draft

        $input = $request->allSafe();

        // we should not reach here because we make this check on client
        // however, an sql error shows that the js client check failed (CPM-2370)
        // so, we put this here for extra safety
        if ( ! isset($input['body']) || null === $input['body']) {
            return response()->json(['error' => 'cannot store draft. body is null']);
        }

        $patient = User::findOrFail($patientId);

        try {
            if ( ! isset($input['status'])) {
                $input['status'] = 'draft';
            }
            $note = $this->saveNote($patient, $input);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }

        return response()->json(['message' => 'success', 'note_id' => $note->id]);
    }

    /**
     * 1. This can be converted to one query.
     * 2. The json_decode is risky at the bottom and more error control is needed.
     *
     * @param $patientId
     *
     * @return |null
     */
    private function getAnswerFromSelfEnrolmentSurvey($patientId)
    {
        $thisYear       = Carbon::now()->year;
        $surveyInstance = DB::table('survey_instances')
            ->join('surveys', 'survey_instances.survey_id', '=', 'surveys.id')
            ->where('name', '=', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
            ->where('year', '=', $thisYear)
            ->first();

        if ( ! $surveyInstance) {
            return null;
        }

        $surveyAnswer = DB::table('questions')
            ->join('answers', 'questions.id', '=', 'answers.question_id')
            ->where('user_id', '=', $patientId)
            ->where('identifier', '=', 'Q_REQUESTS_INFO')
            ->where('survey_instance_id', '=', $surveyInstance->id)
            ->first();

        if ( ! $surveyAnswer) {
            return null;
        }

        // this is risky
        return json_decode($surveyAnswer->value)[0]->name;
    }

    /**
     * Per CPM-2259
     * Default behaviour for attesting patient conditions is: Require at least 1 condition to be attested on call.
     *
     * However:
     *
     * If feature is enabled for practice,
     *
     * If current summary has CCM code && less than 2 conditions have been attested alredy -> require nurse to
     * attest to 2 CCM conditions in the modal.
     * (Make distinction between CCM && BHI only if summary has both BHI && CCM code enabled - is_complex)
     *
     * If current summary has also BHI code && no BHI conditions have been attested already -> require nurse to
     * attest 1 BHI condition along with 2 CCM
     */
    private function getAttestationRequirementsIfYouShould(User $patient)
    {
        $requirements = [
            'disabled'              => true,
            'has_ccm'               => false,
            'has_pcm'               => false,
            'ccm_problems_attested' => 0,
            'bhi_problems_attested' => 0,
        ];

        if ( ! complexAttestationRequirementsEnabledForPractice($patient->primaryPractice->id)) {
            return $requirements;
        }

        $requirements['disabled'] = false;

        if ( ! PatientMonthlySummary::existsForCurrentMonthForPatient($patient)) {
            PatientMonthlySummary::createFromPatient($patient->id, Carbon::now()->startOfMonth());
        }

        /**
         * @var PatientMonthlySummary
         */
        $pms = $patient->patientSummaries()
            ->with([
                //all chargeable services includes un-fulfilled service codes as well as fulfilled.
                'allChargeableServices',
                'attestedProblems',
            ])
            ->getCurrent()
            ->first();

        //if this hasn't had last month's chargeable services attach for some reason, try here
        if ($pms->allchargeableServices->isEmpty()) {
            $pms->attachChargeableServicesToFulfill();
            $pms->load('allChargeableServices');
        }

        $services = $pms->allChargeableServices;

        if ($services->where('code', ChargeableService::CCM)->isNotEmpty()) {
            $requirements['has_ccm'] = true;
        }

        if ($services->where('code', ChargeableService::PCM)->isNotEmpty()) {
            $requirements['has_pcm'] = true;
        }

        $requirements['ccm_problems_attested'] = $pms->ccmAttestedProblems(true)->count();

        $requirements['bhi_problems_attested'] = $pms->bhiAttestedProblems(true)->count();

        return $requirements;
    }

    private function getProviders($getNotesFor)
    {
        return collect($getNotesFor)->map(
            function ($for) {
                $data = explode(':', $for);
                $selectKey = $data[0];
                $practiceOrProviderId = $data[1];

                if ('practice' == $selectKey) {
                    return Practice::getProviders($practiceOrProviderId)->pluck('id')->all();
                }

                return optional(User::find($practiceOrProviderId))->id;
            }
        )
            ->flatten()
            ->all();
    }

    /**
     * @param $input
     *
     * @throws \Exception
     */
    private function saveNote(User $patient, $input): ?Note
    {
        //note_id comes from form, noteId comes from route
        $noteId = $input['note_id'] ?? ($input['noteId'] ?? null);

        //in case Performed By field is removed from the form (per CPM-165)
        if ( ! isset($input['author_id'])) {
            $input['author_id'] = auth()->id();
        }

        $input['performed_at'] = array_key_exists('performed_at', $input) ? Carbon::parse(
            $input['performed_at'],
            $patient->timezone
        )->setTimezone(config('app.timezone'))->toDateTimeString() : now()->toDateTimeString();

        if ($noteId) {
            $note = Note::find($noteId);
            if ( ! $note) {
                throw new \Exception("Could not find note with id $noteId");
            }
            if (Note::STATUS_COMPLETE === $note->status) {
                throw new \Exception('Cannot edit note. Please use create addendum to make corrections.');
            }
            $note = $this->service->editNote($note, $input);
        } else {
            if (isset($input['call_status']) && Call::REACHED === $input['call_status']) {
                $input['successful_clinical_call'] = 1;
            }
            $note = Note::create($input);
        }

        return $note;
    }

    private function sendPatientEmail($input, $patient, $note)
    {
        $address = $patient->email;

        if (isset($input['custom-patient-email'])) {
            $address = $input['custom-patient-email'];

            if (isset($input['default-patient-email'])) {
                $patient->email = $input['custom-patient-email'];
                $patient->save();
            }
        }

        if (Str::contains($address, CcdaImporter::FAMILY_EMAIL_SUFFIX)) {
            $address = CcdaImporter::convertFamilyEmailToValidEmail($address);
        }

        SendSingleNotification::dispatch(new PatientCustomEmail(
            $patient,
            auth()->user()->id,
            $input['patient-email-body'],
            $address,
            isset($input['attachments'])
                ? $input['attachments']
                : [],
            $note->id,
            $input['email-subject']
        ));
    }

    private function updatePatientCallWindows(Patient $info, $input)
    {
        $params = new ParameterBag($input);
        if ($params->get('days') && $params->get('window_start') && $params->get('window_end')) {
            PatientContactWindow::sync(
                $info,
                $params->get('days', []),
                $params->get('window_start'),
                $params->get('window_end')
            );
        }
    }

    private function updatePatientInfo(User $patient, $input)
    {
        //UPDATE USER INFO CHANGES
        $info = $patient->patientInfo;

        if (isset($input['ccm_status']) && in_array(
            $input['ccm_status'],
            [Patient::ENROLLED, Patient::WITHDRAWN, Patient::PAUSED, Patient::WITHDRAWN_1ST_CALL]
        )) {
            $inputCcmStatus = $input['ccm_status'];
            if (Patient::WITHDRAWN === $inputCcmStatus && $patient->onFirstCall()) {
                $inputCcmStatus = Patient::WITHDRAWN_1ST_CALL;
            }

            $info->ccm_status = $inputCcmStatus;

            if (in_array($inputCcmStatus, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
                $withdrawnReason = $input['withdrawn_reason'];
                if ('Other' == $withdrawnReason) {
                    $withdrawnReason = $input['withdrawn_reason_other'];
                }
                $info->withdrawn_reason = $withdrawnReason;
            } else {
                $info->withdrawn_reason = null;
            }
        }

        if (isset($input['general_comment'])) {
            $info->general_comment = $input['general_comment'];
        }

        if (isset($input['frequency'])) {
            $info->preferred_calls_per_month = $input['frequency'];
        }

        if ($info->isDirty()) {
            $info->save();
        }

        return $info;
    }
}
