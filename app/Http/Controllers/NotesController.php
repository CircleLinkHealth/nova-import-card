<?php namespace App\Http\Controllers;

use App\Activity;
use App\Call;
use App\Contracts\ReportFormatter;
use App\Note;
use App\PatientContactWindow;
use App\Repositories\PatientWriteRepository;
use App\Services\Calls\SchedulerService;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\ParameterBag;

class NotesController extends Controller
{

    private $service;
    private $formatter;
    private $patientRepo;

    public function __construct(
        NoteService $noteService,
        ReportFormatter $formatter,
        PatientWriteRepository $patientWriteRepository
    ) {
        $this->service     = $noteService;
        $this->formatter   = $formatter;
        $this->patientRepo = $patientWriteRepository;
    }

    public function index(
        Request $request,
        $patientId,
        $showAll = false
    ) {
        $date = Carbon::now()->subMonth(2);
        if ($showAll == true) {
            $date = 0;
        }

        $patient = User::with([
            'activities'   => function ($q) use ($date) {
                $q->where('logged_from', '=', 'manual_input')
                  ->where('performed_at', '>=', $date)
                  ->with('meta')
                  ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                  ->orderBy('performed_at', 'desc');
            },
            'appointments' => function ($q) use ($date) {
                $q->where('date', '>=', $date);
            },
            'billingProvider',
            'notes'        => function ($q) use ($date) {
                $q->where('performed_at', '>=', $date)
                  ->with(['author', 'call', 'notifications']);
            },
            'patientInfo',
        ])
                       ->findOrFail($patientId);

        //if a patient has no notes for the past 2 months, we load all the results and DON'T display 'show all notes button'
        if ($patient->notes->isEmpty() and $showAll == false){
            $patient->load(['notes' => function($notes){
                $notes->with(['author', 'call', 'notifications']);
            }]);

            $showAll = null;
        }

        $messages = \Session::get('messages');

        $report_data = $this->formatter->formatDataForNotesAndOfflineActivitiesReport($patient);

        $ccm_complex = $patient->isCCMComplex() ?? false;

        return view(
            'wpUsers.patient.note.index',
            [
                'activity_json' => $report_data,
                'patient'       => $patient,
                'messages'      => $messages,
                'ccm_complex'   => $ccm_complex,
                'showAll'       => $showAll
            ]
        );
    }

    public function listing(Request $request)
    {

        $input = $request->all();

        $session_user = auth()->user();

        $providers_for_blog = User::whereIn('id', $session_user->viewableProviderIds())
                                  ->pluck('display_name', 'id')->sort();

        //TIME FILTERS

        //if month and year are selected
        if (isset($input['range'])) {
            //Sub no of months by input
            $months = $input['range'];
            $start  = Carbon::now()->startOfMonth()->subMonth($months)->format('Y-m-d');
            $end    = Carbon::now()->endOfMonth()->format('Y-m-d');
        } //if user resets time
        else {
            $months = 0;
            $start  = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end    = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $only_mailed_notes = (isset($input['mail_filter']))
            ? true
            : false;

        $admin_filter = (isset($input['admin_filter']))
            ? true
            : false;

        //Check to see whether a provider was selected.
        if (isset($input['provider']) && $input['provider'] != '') {
            $provider = User::find($input['provider']);

            if ($only_mailed_notes) {
                $notes = $this->service->getForwardedNotesWithRangeForProvider($provider->id, $start, $end);
            } else {
                $notes = $this->service->getNotesWithRangeForProvider($provider->id, $start, $end);
            }


            $title = $provider->display_name;

            if ( ! empty($notes)) {
                $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
            }

            $data = [
                'filter'             => $input['provider'],
                'notes'              => $notes,
                'title'              => $title,
                'dateFilter'         => $months,
                'results'            => $notes,
                'providers_for_blog' => $providers_for_blog,
                'isProviderSelected' => true,
                'selected_provider'  => $provider,
                'only_mailed_notes'  => $only_mailed_notes,
                'admin_filter'       => $admin_filter,
            ];
        } else {
            if ((auth()->user()->hasRole('administrator') || auth()->user()->hasRole('care-center')) && $admin_filter) {
                //If an admin is viewing this, we show them all
                //notes from all providers who are in the
                //same program as the provider selected.

                $notes = $this->service->getAllForwardedNotesWithRange(Carbon::parse($start), Carbon::parse($end));

                $title = 'All Forwarded Notes';

                if ( ! empty($notes)) {
                    $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
                }

                $data = [
                    'filter'             => 0,
                    'notes'              => $notes,
                    'title'              => $title,
                    'dateFilter'         => $months,
                    'results'            => $notes,
                    'providers_for_blog' => $providers_for_blog,
                    'isProviderSelected' => true,
                    'selected_provider'  => auth()->user(),
                    'only_mailed_notes'  => $only_mailed_notes,
                    'admin_filter'       => $admin_filter,
                ];
            } else { // Not enough data for a report, return only the essentials

                $data = [
                    'filter'             => 0,
                    'title'              => 'No Provider Selected',
                    'notes'              => false,
                    'providers_for_blog' => $providers_for_blog,
                    'isProviderSelected' => false,
                    'only_mailed_notes'  => false,
                    'dateFilter'         => $months,
                ];
            }
        }

        return view('wpUsers.patient.note.list', $data);
    }

    public function create(
        Request $request,
        $patientId
    ) {

        //@todo segregate to helper functions :/

        if ($patientId) {
            // patient view
            $patient = User::find($patientId);
            if ( ! $patient) {
                return response("User not found", 401);
            }

            //set contact flag
            $patient_contact_window_exists = false;

            if (count($patient->patientInfo->contactWindows) != 0) {
                $patient_contact_window_exists = true;
            }

            $patient_name = $patient->fullName;

            //Pull up user's call information.

            //Gather details to generate form

            $careteam_info = $this->service->getPatientCareTeamMembers($patientId);

            if ($patient->timeZone == '') {
                $userTimeZone = 'America/New_York';
            } else {
                $userTimeZone = $patient->timeZone;
            }

            //Check for User's blog
            if (empty($patient->program_id)) {
                return response("User's Program not found", 401);
            }

            //is there any check here? returns true or false
            Auth::user()->hasRole('care-center');

            //providers
            $provider_info = [];

            $author      = Auth::user();
            $author_id   = $author->id;
            $author_name = $author->fullName;

            //Patient Call Windows:
            $window = PatientContactWindow::getPreferred($patient->patientInfo);

            $contact_days_array = [];
            if (is_object($patient->patientInfo->contactWindows)) {
                $contact_days_array = array_merge(explode(',', $patient->patientInfo->preferred_cc_contact_days));
            }

            $ccm_complex = $patient->isCCMComplex() ?? false;

            asort($provider_info);
            asort($careteam_info);

            $view_data = [
                'program_id'         => $patient->program_id,
                'patient'            => $patient,
                'patient_name'       => $patient_name,
                'note_types'         => Activity::input_activity_types(),
                'author_id'          => $author_id,
                'author_name'        => $author_name,
                'careteam_info'      => $careteam_info,
                'userTimeZone'       => $userTimeZone,
                'window'             => $window,
                'window_flag'        => $patient_contact_window_exists,
                'contact_days_array' => $contact_days_array,
                'ccm_complex'        => $ccm_complex,
                'notifies_text'      => $patient->notifies_text,
                'note_channels_text' => $patient->note_channels_text,
            ];

            $isV2 = strpos($request->path(), 'v2') !== false;
            $isV3 = strpos($request->path(), 'v3') !== false;

            if ($isV2) {
                return view('wpUsers.patient.note.create-v2', $view_data);
            }
            else if ($isV3) {
                return view('wpUsers.patient.note.create-v3', $view_data);
            }
            else {
                return view('wpUsers.patient.note.create', $view_data);
            }
        }
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
     * They are never redirected to Schedule Next Calll page.
     *
     * @param Request $input
     * @param SchedulerService $schedulerService
     * @param $patientId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        Request $input,
        SchedulerService $schedulerService,
        $patientId
    ) {

        $input = $input->all();

        //in case Performed By field is removed from the form (per CPM-165)
        if (!isset($input['author_id'])) {
            $input['author_id'] = auth()->id();
        }
        $input['performed_at'] = Carbon::parse($input['performed_at'])->toDateTimeString();

        $note = $this->service->storeNote($input);

        $patient = User::where('id', $patientId)->first();

        //UPDATE USER INFO CHANGES
        $info = $patient->patientInfo;

        if (isset($input['status'])) {
            $info->ccm_status = $input['status'];
        }

        //CCM Complexity Handle
        $this->service->updatePatientRecords($patient->patientInfo, isset($input['complex']));

        if (isset($input['general_comment'])) {
            $info->general_comment = $input['general_comment'];
        }

        if (isset($input['frequency'])) {
            $info->preferred_calls_per_month = $input['frequency'];
        }

        $info->save();

        // also update patientCallWindows @todo - do this only
        $params = new ParameterBag($input);

        if ($params->get('days') && $params->get('window_start') && $params->get('window_end')) {
            PatientContactWindow::sync(
                $info,
                $params->get('days', []),
                $params->get('window_start'),
                $params->get('window_end')
            );
        }

        /**
         * If phone call AND:
         *   - if a nurse (care-center): update today's call and check if should redirect to schedule next call page
         *   - if any other role: store a call
         */
        $is_phone_session = isset($input['phone']);

        if (Auth::user()->hasRole('care-center')) {

            $is_withdrawn = $info->ccm_status == 'withdrawn';

            if (!$is_phone_session && $is_withdrawn) {
                return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
                    'messages',
                    ['Successfully Created Note']
                );
            }

            if ($is_phone_session) {

                if (!isset($input['call_status'])) {
                    //exit with error
                    return redirect()
                        ->back()
                        ->withErrors(["Invalid form input. Missing ['call_status']"])
                        ->withInput();
                }

                $call_status = $input['call_status'];
                $is_saas = auth()->user()->isSaas();
                $prediction = null;

                if ($call_status == Call::REACHED) {
                    //Updates when the patient was successfully contacted last
                    $info->last_successful_contact_time = Carbon::now()->format('Y-m-d H:i:s'); // @todo add H:i:s
                }

                if (!$is_saas && !$is_withdrawn) {
                    $prediction = $schedulerService->updateTodaysCallAndPredictNext($patient, $note->id, $call_status);
                }

                // add last contact time regardless of if success
                $info->last_contact_time = Carbon::now()->format('Y-m-d H:i:s');
                $info->save();

                if ($is_withdrawn || $prediction == null || $is_saas) {
                    return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
                        'messages',
                        ['Successfully Created Note']
                    );
                }

                $seconds = $patient->patientInfo()->first()->cur_month_activity_time;

                $ccm_complex = $patient->isCCMComplex() ?? false;

                $ccm_above = false;
                if ($seconds > 1199 && ! $ccm_complex) {
                    $ccm_above = true;
                } elseif ($seconds > 3599 && $ccm_complex) {
                    $ccm_above = true;
                }

                $prediction['ccm_above']   = $ccm_above;
                $prediction['ccm_complex'] = $ccm_complex;

                return view('wpUsers.patient.calls.create', $prediction);
            }
        }

        //If successful phone call and provider, also mark as the last successful day contacted. [ticket: 592]
        if ($is_phone_session) {

            if (isset($input['call_status']) && $input['call_status'] == 'reached') {
                if (auth()->user()->hasRole('provider')) {
                    $this->service->storeCallForNote($note, 'reached', $patient, Auth::user(), Auth::user()->id, null);

                    $this->patientRepo->updateCallLogs($patient->patientInfo, true);

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

                    $info->date_welcomed = Carbon::now()->format('Y-m-d H:i:s');
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

        return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
            'messages',
            ['Successfully Created Note']
        );
    }

    public function show(
        Request $input,
        $patientId,
        $noteId
    ) {

        $patient = User::find($patientId);
        $note    = Note::where('id', $noteId)
                       ->with(['call', 'notifications'])
                       ->first();

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
            $data['provider_name'] = $provider->fullName;
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
            'notifies_text'      => $patient->notifies_text,
            'note_channels_text' => $patient->note_channels_text,
        ];

        return view('wpUsers.patient.note.view', $view_data);
    }

    public function send(
        Request $input,
        $patientId,
        $noteId
    ) {
        $note = Note::findOrFail($input['noteId']);

        $note->forward($input['notify_careteam'], $input['notify_circlelink_support']);

        return redirect()->route('patient.note.index', ['patient' => $patientId]);
    }

    public function storeAddendum(
        Request $request,
        $patientId,
        $noteId
    ) {
        $this->validate($request, [
            'addendum-body' => 'required',
        ]);

        $note = Note::find($noteId)->addendums()->create([
            'body'           => $request->input('addendum-body'),
            'author_user_id' => auth()->user()->id,
        ]);

        return redirect()->to(route('patient.note.view',
                ['patientId' => $patientId, 'noteId' => $noteId]) . '#create-addendum');
    }
}
