<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Call;
use App\Contracts\ReportFormatter;
use App\Http\Requests\NotesReport;
use App\Note;
use App\Repositories\PatientWriteRepository;
use App\SafeRequest;
use App\Services\Calls\SchedulerService;
use App\Services\CPM\CpmMedicationService;
use App\Services\NoteService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\ParameterBag;

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
        Request $request,
        $patientId,
        $noteId = null,
        CpmMedicationService $medicationService
    ) {
        //@todo segregate to helper functions :/

        if ( ! $patientId) {
            return response('Missing param: patientId', 401);
        }

        // patient view
        $patient = User::find($patientId);
        if ( ! $patient) {
            return response('User not found', 401);
        }

        //set contact flag
        $patient_contact_window_exists = false;

        if (0 != count($patient->patientInfo->contactWindows)) {
            $patient_contact_window_exists = true;
        }

        $patient_name = $patient->getFullName();

        //Pull up user's call information.

        //Gather details to generate form

        $careteam_info = $this->service->getPatientCareTeamMembers($patientId);

        $userTimeZone = empty($patient->timezone)
            ? 'America/New_York'
            : $patient->timezone;

        //Check for User's blog
        if (empty($patient->program_id)) {
            return response("User's Program not found", 401);
        }

        //providers
        $provider_info = [];

        $author      = Auth::user();
        $author_id   = $author->id;
        $author_name = $author->getFullName();

        //Patient Call Windows:
        $window = PatientContactWindow::getPreferred($patient->patientInfo);

        $contact_days_array = [];
        if (is_object($patient->patientInfo->contactWindows)) {
            $contact_days_array = array_merge(explode(',', $patient->patientInfo->preferred_cc_contact_days));
        }

        asort($provider_info);
        asort($careteam_info);

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

        $isCareCoach = Auth::user()->isCareCoach();
        $meds        = [];
        if ($isCareCoach && $this->shouldPrePopulateWithMedications($patient)) {
            $meds = $medicationService->repo()->patientMedicationsList($patientId);
        }

        $reasons = [
            'No Longer Interested',
            'Moving out of Area',
            'New Physician',
            'Cost / Co-Pay',
            'Changed Insurance',
            'Dialysis / End-Stage Renal Disease',
            'Expired',
            'Home Health Services',
            'Other',
        ];

        $withdrawnReasons       = array_combine($reasons, $reasons);
        $patientWithdrawnReason = $patient->getWithdrawnReason();

        if ($noteId) {
            $existingNote = Note::findOrFail($noteId);
        } else {
            $existingNote = Note::where('patient_id', '=', $patientId)
                ->where('author_id', '=', $author_id)
                ->where('status', '=', Note::STATUS_DRAFT)
                ->first();
        }

        $existingCall = $existingNote
            ? Call::where('note_id', '=', $existingNote->id)->first()
            : null;

        $view_data = [
            'program_id'             => $patient->program_id,
            'patient'                => $patient,
            'patient_name'           => $patient_name,
            'note_types'             => Activity::input_activity_types(),
            'task_types_to_topics'   => Activity::task_types_to_topics(),
            'tasks'                  => $nurse_patient_tasks,
            'author_id'              => $author_id,
            'author_name'            => $author_name,
            'careteam_info'          => $careteam_info,
            'userTimeZone'           => $userTimeZone,
            'window'                 => $window,
            'window_flag'            => $patient_contact_window_exists,
            'contact_days_array'     => $contact_days_array,
            'notifies_text'          => $patient->getNotifiesText(),
            'note_channels_text'     => $patient->getNoteChannelsText(),
            'medications'            => $meds,
            'withdrawnReasons'       => $withdrawnReasons,
            'patientWithdrawnReason' => $patientWithdrawnReason,
            'note'                   => $existingNote,
            'call'                   => $existingCall,
        ];

        return view('wpUsers.patient.note.create', $view_data);
    }

    public function index(
        Request $request,
        $patientId,
        $showAll = false
    ) {
        $date = Carbon::now()->subMonth(2);
        if (true == $showAll) {
            $date = 0;
        }

        $patient = User::with(
            [
                'activities' => function ($q) use ($date) {
                    $q->where('logged_from', '=', 'manual_input')
                        ->where('performed_at', '>=', $date)
                        ->with('meta')
                        ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
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
        $session_user = auth()->user();

        if ($request->has('getNotesFor')) {
            $providers = $this->getProviders($request->getNotesFor);
        }

        $data['providers'] = User::whereIn('id', $session_user->viewableProviderIds())
            ->pluck('display_name', 'id')->sort();
        $data['practices'] = Practice::whereIn('id', $session_user->viewableProgramIds())
            ->pluck('display_name', 'id')->sort();

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
        Request $input,
        $patientId,
        $noteId
    ) {
        $note = Note::where('patient_id', $input['patient_id'])
            ->findOrFail($input['noteId']);

        $note->forward($input['notify_careteam'], $input['notify_circlelink_support']);

        return redirect()->route('patient.note.index', ['patient' => $patientId]);
    }

    public function show(
        $patientId,
        $noteId
    ) {
        $note = Note::where('id', $noteId)
            ->where('patient_id', $patientId)
            ->with(['call', 'notifications', 'patient'])
            ->firstOrFail();

        $patient = $note->patient;

        $this->service->markNoteAsRead(auth()->user(), $note);

        $readers = $this->service->getSeenForwards($note);

        //Set up note packet for view
        $data = [];

        //Sets up tags for patient note tags
        $meta_tags = $this->service->tags($note);

        $data['type']         = $note->type;
        $data['id']           = $note->id;
        $data['performed_at'] = $note->performed_at;
        $provider             = User::find($note->author_id);
        if ($provider) {
            $data['provider_name'] = $provider->getFullName();
        } else {
            $data['provider_name'] = '';
        }

        $data['comment']   = $note->body;
        $data['addendums'] = $note->addendums->sortByDesc('created_at');

        $careteam_info = $this->service->getPatientCareTeamMembers($patientId);

        asort($careteam_info);

        $view_data = [
            'note'               => $data,
            'userTimeZone'       => $patient->timeZone,
            'careteam_info'      => $careteam_info,
            'patient'            => $patient,
            'program_id'         => $patient->program_id,
            'meta'               => $meta_tags,
            'hasReaders'         => $readers->all(),
            'notifies_text'      => $patient->getNotifiesText(),
            'note_channels_text' => $patient->getNoteChannelsText(),
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
     * @param SafeRequest      $request
     * @param SchedulerService $schedulerService
     * @param $patientId
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

        $noteId = ! empty($input['note_id'])
            ? $input['note_id']
            : null;

        $input['status'] = 'complete';

        //in case Performed By field is removed from the form (per CPM-165)
        if ( ! isset($input['author_id'])) {
            $input['author_id'] = auth()->id();
        }
        $input['performed_at'] = Carbon::parse($input['performed_at'])->toDateTimeString();

        if ($noteId) {
            $note = $this->editNote($noteId, $input);
        } else {
            $note = $this->service->storeNote($input);
        }

        $patient = User::findOrFail($patientId);

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
                if ('done' === $task_status) {
                    if ('Call Back' === $call->sub_type) {
                        if ( ! isset($input['call_status'])) {
                            return redirect()
                                ->back()
                                ->withErrors(["Invalid form input. Missing ['call_status']"])
                                ->withInput();
                        }

                        $call->status = $input['call_status'];

                        //Updates when the patient was successfully contacted last
                        //use $note->created_at, in case we are editing a note
                        $info->last_successful_contact_time = $note->performed_at->format('Y-m-d H:i:s');

                        //took this from below :)
                        if (auth()->user()->hasRole('provider')) {
                            $this->patientRepo->updateCallLogs($patient->patientInfo, true, $note->performed_at);
                        }
                    } else {
                        $call->status = 'done';
                    }
                }

                if ('Call Back' === $call->sub_type) {
                    // add last contact time regardless of if success
                    $info->last_contact_time = $note->performed_at->format('Y-m-d H:i:s');
                    $info->save();
                }

                $call->note_id = $note->id;

                if ($call->isDirty()) {
                    $call->save();
                }
            }
        } else {
            if (Auth::user()->isCareCoach()) {
                $is_withdrawn = 'withdrawn' == $info->ccm_status;

                if ( ! $is_phone_session && $is_withdrawn) {
                    return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
                        'messages',
                        ['Successfully Created Note']
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
                    $is_saas     = auth()->user()->isSaas();
                    $prediction  = null;

                    if (Call::REACHED == $call_status) {
                        //Updates when the patient was successfully contacted last
                        $info->last_successful_contact_time = $note->performed_at->format('Y-m-d H:i:s');
                    }

                    if ( ! $is_saas && ! $is_withdrawn) {
                        $prediction = $schedulerService->updateTodaysCallAndPredictNext(
                            $patient,
                            $note->id,
                            $call_status
                        );
                    }

                    // add last contact time regardless of if success
                    $info->last_contact_time = $note->performed_at->format('Y-m-d H:i:s');
                    $info->save();

                    if ($is_withdrawn || null == $prediction || $is_saas) {
                        return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
                            'messages',
                            ['Successfully Created Note']
                        );
                    }

                    $seconds = $patient->getCcmTime();

                    $ccm_above = false;
                    if ($seconds > 1199) {
                        $ccm_above = true;
                    } elseif ($seconds > 3599) {
                        $ccm_above = true;
                    }

                    $prediction['ccm_above'] = $ccm_above;

                    return view('wpUsers.patient.calls.create', $prediction);
                }
            }

            //If successful phone call and provider, also mark as the last successful day contacted. [ticket: 592]
            if ($is_phone_session) {
                if (isset($input['call_status']) && 'reached' == $input['call_status']) {
                    if (auth()->user()->hasRole('provider')) {
                        $this->service->storeCallForNote(
                            $note,
                            'reached',
                            $patient,
                            Auth::user(),
                            Auth::user()->id,
                            null
                        );

                        $this->patientRepo->updateCallLogs($patient->patientInfo, true, $note->performed_at);

                        $info->last_successful_contact_time = Carbon::now()->format('Y-m-d H:i:s');
                        $info->save();
                    }
                }

                if (auth()->user()->hasRole('no-ccm-care-center')) {
                    if (isset($input['welcome_call'])) {
                        $this->service->storeCallForNote(
                            $note,
                            'welcome call',
                            $patient,
                            auth()->user(),
                            auth()->user()->id,
                            null
                        );

                        $info->date_welcomed = $note->performed_at->format('Y-m-d H:i:s');
                        $info->save();
                    } else {
                        $this->service->storeCallForNote(
                            $note,
                            'welcome attempt',
                            $patient,
                            auth()->user(),
                            auth()->user()->id,
                            null
                        );
                    }

                    if (isset($input['other_call'])) {
                        $this->service->storeCallForNote(
                            $note,
                            'other call',
                            $patient,
                            auth()->user(),
                            auth()->user()->id,
                            null
                        );
                    }
                }
            }
        }

        return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
            'messages',
            ['Successfully Created Note']
        );
    }

    public function storeAddendum(
        Request $request,
        $patientId,
        $noteId
    ) {
        $this->validate(
            $request,
            [
                'addendum-body' => 'required',
            ]
        );

        $note = Note::find($noteId)->addendums()->create(
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

        $noteId = ! empty($input['note_id'])
            ? $input['note_id']
            : null;

        //in case Performed By field is removed from the form (per CPM-165)
        if ( ! isset($input['author_id'])) {
            $input['author_id'] = auth()->id();
        }
        $input['performed_at'] = Carbon::parse($input['performed_at'])->toDateTimeString();

        if ($noteId) {
            $note = $this->editNote($noteId, $input);
        } else {
            $input['status'] = 'draft';
            $note            = $this->service->storeNote($input);
        }

        return response()->json(['message' => 'success', 'note_id' => $note->id]);
    }

    private function editNote($noteId, $requestInput)
    {
        $note            = Note::find($noteId);
        $note->logger_id = $requestInput['logger_id'];
        $note->isTCM     = isset($requestInput['isTCM'])
            ? $requestInput['isTCM']
            : 0;
        $note->type                 = $requestInput['type'];
        $note->body                 = $requestInput['body'];
        $note->performed_at         = $requestInput['performed_at'];
        $note->did_medication_recon = isset($requestInput['did_medication_recon'])
            ? $requestInput['did_medication_recon']
            : 0;
        if ($note->isDirty()) {
            $note->save();
        }

        return $note;
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

    private function shouldPrePopulateWithMedications(User $patient)
    {
        return Practice::whereId($patient->program_id)
            ->where(
                function ($q) {
                    $q->where('name', '=', 'phoenix-heart')
                        ->orWhere('name', '=', 'demo');
                }
                       )
            ->exists();
    }

    private function storeInternal(
        SafeRequest $request,
        SchedulerService $schedulerService,
        $patientId
    ) {
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

        if (isset($input['status'])) {
            $info->ccm_status = $input['status'];

            if ('withdrawn' == $input['status']) {
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

        $info->save();

        return $info;
    }
}
