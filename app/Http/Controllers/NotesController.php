<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\Formatters\WebixFormatter;
use App\Http\Requests;
use App\Program;
use App\Services\ActivityService;
use App\Services\NoteService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Laracasts\Flash\Flash;

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

        $isDateFiltered = true;

        //TIME FILTERS

        //if month and year are selected
        if (isset($input['selectMonth'])) {
            $time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $start = $time->startOfMonth()->format('Y-m-d');
            $end = $time->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');

            //time title for view
            $time_title = $month_selected_text . ' ' . $year_selected;

        } //if user resets time
            else
        {
            $time = Carbon::now();
            $start = $time->startOfMonth()->format('Y-m-d');
            $end = $time->endOfMonth()->format('Y-m-d');
            $month_selected_text = $time->format('F');
            $month_selected = $time->format('m');
            $year_selected = $time->format('Y');

            $time_title = $month_selected_text . ' ' . $year_selected;
            //page first loads
        }

        $years = $this->service->getYearsArray();
        $months = $this->service->getMonthsArray();


        //Get Viewable Patients
        $patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
            ->with('phoneNumbers', 'patientInfo', 'patientCareTeamMembers')->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })->get()->lists('ID')->all();

        $notes = $this->service->getNotesWithRangeForPatients($patients, $start, $end);

        if (!empty($notes)) {

            $notes = $this->formatter->formatDataForNotesListingReport($notes, $request);
        }dd($notes);

        $data = ['years' => array_reverse($years),
            'month_selected' => $month_selected,
            'year_selected' => $year_selected,
            'month_selected_text' => $month_selected_text,
            'months' => $months,
            'notes' => $notes,
            'time_title' => $time_title,
            'dateFilter' => $isDateFiltered,
            'results' => $notes
        ];

        return view('wpUsers.patient.note.list', $data);

    }

    public function create(Request $request, $patientId)
    {

        if ($patientId) {
            // patient view
            $user = User::find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }

            $patient_name = $user->fullName;

            //Gather details to generate form

            //careteam
            $careteam_info = array();
            $careteam_ids = $user->careTeam;
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

            if ($user->timeZone == '') {
                $userTimeZone = 'America/New_York';
            } else {
                $userTimeZone = $user->timeZone;
            }

            //Check for User's blog
            if (empty($user->blogId())) {
                return response("User's Program not found", 401);
            }

            //providers
            $providers = Program::getProviders($user->blogId());
            $nonCCMCareCenterUsers = Program::getNonCCMCareCenterUsers($user->blogId());
            $careCenterUsers = Program::getCareCenterUsers($user->blogId());
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
            asort($provider_info);
            asort($careteam_info);

            $view_data = [
                'program_id' => $user->blogId(),
                'patient' => $user,
                'patient_name' => $patient_name,
                'note_types' => Activity::input_activity_types(),
                'author_id' => $author_id,
                'author_name' => $author_name,
                'careteam_info' => $careteam_info,
                'userTimeZone' => $userTimeZone
            ];

            return view('wpUsers.patient.note.create', $view_data);
        }
    }

    public function store(Request $input, $patientId)
    {

        $input = $input->all();
        $input['performed_at'] = Carbon::parse($input['performed_at'])->toDateTimeString();

        $this->service->storeNote($input);

        return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Successfully Created Note']);
    }

    public function show(Request $input, $patientId, $noteId)
    {

        $patient = User::find($patientId);
        $note = $this->service->getNoteWithCommunications($noteId);

        //Set up note packet for view
        $data = array();

        //Sets up tags for patient note tags
        $meta_tags = array();

        //Call Info
        if (($note->call != null)) {
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

    public function send(Request $input, $patientId, $noteId)
    {
        $input = $input->all();

        $this->service->forwardNote($input, $patientId);

        return redirect()->route('patient.note.index', ['patient' => $patientId]);
    }
}

