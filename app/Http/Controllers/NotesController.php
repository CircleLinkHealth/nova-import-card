<?php namespace App\Http\Controllers;

use App\Activity;
use App\Formatters\WebixFormatter;
use App\Http\Requests;
use App\PatientInfo;
use App\Program;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laracasts\Flash\Flash;
use App\Services\Calls\SchedulerService;

class NotesController extends Controller
{

    private $service;
    private $formatter;

    public function __construct(NoteService $noteService, WebixFormatter $formatter)
    {
        $this->service = $noteService;
        $this->formatter = $formatter;
    }

    public function index(Request $request, $patientId)
    {

        $patient = User::find($patientId);
        $messages = \Session::get('messages');

        $data = $this->service->getNotesAndOfflineActivitiesForPatient($patient);

        $report_data = $this->formatter->formatDataForNotesAndOfflineActivitiesReport($data);

        if ($report_data == '') {
            $data = false;
        }
//        dd($report_data);

        return view('wpUsers.patient.note.index',
            ['activity_json' => $report_data,
                'patient' => $patient,
                'messages' => $messages,
                'data' => $data
            ]);

    }

    public function listing(Request $request)
    {

        $input = $request->all();


        $session_user = Auth::user();

        $providers_for_blog = User::whereIn('ID', $session_user->viewableProviderIds())->lists('display_name','ID')->sort();

        //TIME FILTERS

        //if month and year are selected
        if (isset($input['range'])) {
            //Sub no of months by input
            $months = $input['range'];
            $start = Carbon::now()->startOfMonth()->subMonth($months)->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        } //if user resets time
        else {
            $months = 0;
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        if (isset($input['mail_filter'])) {

            $only_mailed_notes = true;

        } else {

            $only_mailed_notes = false;

        }


        //Check to see whether a provider was selected.
        if (isset($input['provider']) && $input['provider'] != '') {

            $provider = User::find($input['provider']);

            $title = $provider->display_name;

            if($only_mailed_notes){
                $notes = $this->service->getForwardedNotesWithRangeForProvider($provider->ID, $start, $end);

            } else {
                $notes = $this->service->getNotesWithRangeForProvider($provider->ID, $start, $end);

            }

            if (!empty($notes)) {

                $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
            }

            $data = [
                'filter' => $input['provider'],
                'notes' => $notes,
                'title' => $title,
                'dateFilter' => $months,
                'results' => $notes,
                'providers_for_blog' => $providers_for_blog,
                'isProviderSelected' => true,
                'selected_provider' => $provider,
                'only_mailed_notes' => $only_mailed_notes
            ];

        } else { // Not enough data for a report, return only the essentials

            $data = [
                'filter' => 0,
                'title' => 'No Provider Selected',
                'notes' => false,
                'providers_for_blog' => $providers_for_blog,
                'isProviderSelected' => false,
                'only_mailed_notes' => false
            ];

        }

        return view('wpUsers.patient.note.list', $data);

    }

    public function create(Request $request, $patientId)
    {

        if ($patientId) {
            // patient view
            $patient = User::find($patientId);
            if (!$patient) {
                return response("User not found", 401);
            }

            $patient_name = $patient->fullName;

            //Gather details to generate form

            //careteam
            $careteam_info = array();
            $careteam_ids = $patient->careTeam;
            if ((@unserialize($careteam_ids) !== false)) {
                $careteam_ids = unserialize($careteam_ids);
            }
            if (!empty($careteam_ids) && is_array($careteam_ids)) {
                foreach ($careteam_ids as $id) {
                    if (User::find($id)) {
                        $careteam_info[$id] = User::find($id)->fullName;
                    }
                }
            }

            if ($patient->timeZone == '') {
                $userTimeZone = 'America/New_York';
            } else {
                $userTimeZone = $patient->timeZone;
            }

            //Check for User's blog
            if (empty($patient->blogId())) {
                return response("User's Program not found", 401);
            }

            //providers
            $providers = Program::getProviders($patient->blogId());
            $nonCCMCareCenterUsers = Program::getNonCCMCareCenterUsers($patient->blogId());
            $careCenterUsers = Program::getCareCenterUsers($patient->blogId());
            $provider_info = array();

//            if(!empty($providers)) {
//                foreach ($providers as $provider) {
//                    if($provider->fullName) {
//                        $provider_info[$provider->ID] = $provider->fullName;
//                    }
//                }
//            }

            $author = Auth::user();
            $author_id = $author->ID;
            $author_name = $author->fullName;

            if (!empty($nonCCMCareCenterUsers)) {
                foreach ($nonCCMCareCenterUsers as $nonCCMCareCenterUser) {
                    if ($nonCCMCareCenterUser->fullName) {
                        $provider_info[$nonCCMCareCenterUser->ID] = $nonCCMCareCenterUser->fullName;
                    }
                }
            }

            if (!empty($careCenterUsers)) {
                foreach ($careCenterUsers as $careCenterUser) {
                    if ($careCenterUser->fullName) {
                        $provider_info[$careCenterUser->ID] = $careCenterUser->fullName;
                    }
                }
            }

            //Add care center users to Performed By Drop Down
            if (!empty($careteam_info)) {
                foreach ($careteam_info as $careteam_member) {
                    array_push($provider_info, $careteam_member);
                }
            }
            
            //Patient Call Windows:
            $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);
            
            asort($provider_info);
            asort($careteam_info);

            $view_data = [
                'program_id' => $patient->blogId(),
                'patient' => $patient,
                'patient_name' => $patient_name,
                'note_types' => Activity::input_activity_types(),
                'author_id' => $author_id,
                'author_name' => $author_name,
                'careteam_info' => $careteam_info,
                'userTimeZone' => $userTimeZone,
                'window' => $window
            ];

            return view('wpUsers.patient.note.create', $view_data);
        }
    }

    public function store(Request $input, $patientId)
    {
        $input = $input->all();

//        dd($input);

        $input['performed_at'] = Carbon::parse($input['performed_at'])->toDateTimeString();

        $this->service->storeNote($input);

        $patient = User::where('ID',$patientId)->first();

        //Update patient info changes

        $info = $patient->patientInfo;

        $info->general_comment = $input['general_comment'];
        $info->daily_contact_window_start = $input['window_start'];
        $info->daily_contact_window_end = $input['window_end'];
        $info->preferred_calls_per_month = $input['frequency'];
        $info->preferred_cc_contact_days = implode(', ',$input['days']);

        $info->save();


        if(isset($input['call_status']) && $input['call_status'] == 'reached'){

            $prediction = (new SchedulerService)->scheduleCall($patient);

            return view('wpUsers.patient.calls.create', $prediction);

        }

        return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Successfully Created Note']);
    }

    public
    function show(Request $input, $patientId, $noteId)
    {

        $patient = User::find($patientId);
        $note = $this->service->getNoteWithCommunications($noteId);

        //Set up note packet for view
        $data = array();

        //Sets up tags for patient note tags
        $meta_tags = array();

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

        if ($note->isTCM) {
            $meta_tags[] = 'Patient Recently in Hospital/ER';
        }


        $data['type'] = $note->type;
        $data['id'] = $note->id;
        $data['performed_at'] = $note->performed_at;
        $provider = User::find($note->author_id);
        if ($provider) {
            $data['provider_name'] = $provider->fullName;
        } else {
            $data['provider_name'] = '';
        }

        $data['comment'] = $note->body;

        $careteam_info = array();
        $careteam_ids = $patient->careTeam;
        if ((@unserialize($careteam_ids) !== false)) {
            $careteam_ids = unserialize($careteam_ids);
        }
        if (!empty($careteam_ids) && is_array($careteam_ids)) {
            foreach ($careteam_ids as $id) {
                $careteam_info[$id] = User::find($id) ? User::find($id)->getFullNameAttribute() : '';
            }
        }

        asort($careteam_info);

        $view_data = [
            'note' => $data,
            'userTimeZone' => $patient->timeZone,
            'careteam_info' => $careteam_info,
            'patient' => $patient,
            'program_id' => $patient->blogId(),
            'meta' => $meta_tags
        ];

        return view('wpUsers.patient.note.view', $view_data);
    }

    public
    function send(Request $input, $patientId, $noteId)
    {
        $input = $input->all();

        $this->service->forwardNote($input, $patientId);

        return redirect()->route('patient.note.index', ['patient' => $patientId]);
    }
}

