<?php namespace App\Http\Controllers;

use App\Activity;
use App\Formatters\WebixFormatter;
use App\Note;
use App\PatientContactWindow;
use App\PatientMonthlySummary;
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

    public function __construct(
        NoteService $noteService,
        WebixFormatter $formatter
    ) {
        $this->service   = $noteService;
        $this->formatter = $formatter;
    }

    public function index(
        Request $request,
        $patientId
    ) {

        $patient = User::where('id', $patientId)
                       ->with([
                           'activities' => function ($q) {
                               return $q->where('logged_from', '=', 'manual_input')
                                        ->with('meta')
                                        ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                                        ->orderBy('performed_at', 'desc');
                           },
                           'appointments',
                           'billingProvider',
                           'notes.author',
                           'notes.call',
                           'notes.notifications',
                           'patientInfo',
                       ])->orderByDesc('created_at')
                       ->first();

        $messages = \Session::get('messages');

        $report_data = $this->formatter->formatDataForNotesAndOfflineActivitiesReport($patient);

        $ccm_complex = $patient->patientInfo->isCCMComplex() ?? false;

        return view(
            'wpUsers.patient.note.index',
            [
                'activity_json' => $report_data,
                'patient'       => $patient,
                'messages'      => $messages,
                'ccm_complex'   => $ccm_complex,
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
                    'filter'             => $input['provider'],
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

            $ccm_complex = $patient->patientInfo->isCCMComplex() ?? false;

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

            ];

            return view('wpUsers.patient.note.create', $view_data);
        }
    }

    public function store(
        Request $input,
        $patientId
    ) {

        $input = $input->all();

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
         * If the note wasn't a phone call, redirect to Notes/Offline Activities page
         * If the note was a successful or unsuccessful call, take to prediction
         * engine, then to call create
         */

        if (Auth::user()->hasRole('care-center')) {
            //If the patient was just withdrawn, let's redirect them back to notes.index
            if ($info->ccm_status == 'withdrawn') {
                return redirect()->route('patient.note.index', ['patient' => $patientId])->with(
                    'messages',
                    ['Successfully Created Note']
                );
            }

            if (isset($input['phone'])) {
                if (isset($input['call_status']) && $input['call_status'] == 'reached') {
                    //Updates when the patient was successfully contacted last
                    $info->last_successful_contact_time = Carbon::now()->format('Y-m-d H:i:s'); // @todo add H:i:s

                    $prediction = (new SchedulerService())->getNextCall($patient, $note->id, true);
                } else {
                    $prediction = (new SchedulerService())->getNextCall($patient, $note->id, false);
                }

                // add last contact time regardless of if success
                $info->last_contact_time = Carbon::now()->format('Y-m-d H:i:s');
                $info->save();

                $seconds = $patient->patientInfo()->first()->cur_month_activity_time;

                $ccm_complex = $patient->patientInfo->isCCMComplex() ?? false;

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
        if (isset($input['phone'])) {
            if (isset($input['call_status']) && $input['call_status'] == 'reached') {
                if (auth()->user()->hasRole('provider')) {
                    $this->service->storeCallForNote($note, 'reached', $patient, Auth::user(), Auth::user()->id, null);

                    (new PatientMonthlySummary())->updateCallInfoForPatient($patient->patientInfo, true);

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

        $this->service->updateMailLogsForNote(auth()->user(), $note);

        $readers = $this->service->getSeenForwards($note);

        //Set up note packet for view
        $data = [];

        //Sets up tags for patient note tags
        $meta_tags = [];

        //Call Info
        if (count($note->call) > 0) {
            if ($note->call->is_cpm_inbound) {
                $meta_tags[] = 'Inbound Call';
            } else {
                $meta_tags[] = 'Outbound Call';
            }

            if ($note->call->status == 'reached') {
                $meta_tags[] = 'Successful Clinical Call';
            }
        }

        if ($note->mail->count() > 0) {
            $mailText = 'Forwarded';
            foreach ($note->mail as $mail) {
                if ($mail->receiverUser) {
                    $mailText .= ' ' . $mail->receiverUser->display_name . ',';
                }
            }
            $meta_tags[] = rtrim($mailText, ',');
        }

        if ($note->isTCM) {
            $meta_tags[] = 'Patient Recently in Hospital/ER';
        }

        if ($note->did_medication_recon) {
            $meta_tags[] = 'Medication Reconciliation';
        }

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
            'note'          => $data,
            'userTimeZone'  => $patient->timeZone,
            'careteam_info' => $careteam_info,
            'patient'       => $patient,
            'program_id'    => $patient->program_id,
            'meta'          => $meta_tags,
            'hasReaders'    => $readers,
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
