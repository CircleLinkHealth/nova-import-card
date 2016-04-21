<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\CLH\Facades\StringManipulation;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\ActivityService;
use App\WpBlog;
use App\User;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Laracasts\Flash\Flash;

class NotesController extends Controller
{
    /**
     * Display a listing of the notes for a user.
     *
     * @return Response
     */
    public function index(Request $request, $patientId)
    {

        $patient = User::find($patientId);
        $messages = \Session::get('messages');

        $acts = DB::table('lv_activities')
            ->select(DB::raw('*,provider_id, type'))
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->where('logged_from', 'note')
                    ->Orwhere('logged_from', 'manual_input');
            })
            ->orderBy('performed_at', 'desc')
            ->get();


        $acts = json_decode(json_encode($acts), true);

        foreach ($acts as $key => $value) {
            $acts[$key]['patient'] = User::find($patientId);
        }

        foreach ($acts as $key => $value) {
            $act_id = $acts[$key]['id'];
            $acts_ = Activity::find($act_id);
            $comment = $acts_->getActivityCommentFromMeta($act_id);
            $acts[$key]['comment'] = $comment;
        }

        $activities_data_with_users = array();
        $activities_data_with_users[$patientId] = $acts;

        $reportData[$patientId] = array();
        foreach ($activities_data_with_users as $patientAct) {
            $reportData[] = collect($patientAct)->groupBy('performed_at_year_month');
        }

        for ($i = 0; $i < count($patientAct) - 1; $i++) {
            $logger_user = User::find($patientAct[$i]['logger_id']);
            if ($logger_user) {
                $patientAct[$i]['logger_name'] = $logger_user->getFullNameAttribute();
            } else {
                $patientAct[$i]['logger_name'] = 'N/A';
            }
        }
        $data = true;
        $reportData = "data:" . json_encode($patientAct) . "";
        if ($patientAct == null) {
            $data = false;
        }

        return view('wpUsers.patient.note.index',
            ['activity_json' => $reportData,
                'patient' => $patient,
                'messages' => $messages,
                'data' => $data
            ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request, $patientId)
    {

        if ($patientId) {
            // patient view
            $user = User::find($patientId);
            if (!$user) {
                return response("User not found", 401);
            }

            $patient_name = $user->getFullNameAttribute();

            //Gather details to generate form

            //careteam
            $careteam_info = array();
            $careteam_ids = $user->careTeam;
            if ((@unserialize($careteam_ids) !== false)) {
                $careteam_ids = unserialize($careteam_ids);
            }
            if(!empty($careteam_ids) && is_array($careteam_ids)) {
                foreach ($careteam_ids as $id) {
                    $careteam_info[$id] = User::find($id)->getFullNameAttribute();;
                }//debug($careteam_info);
            }

            if($user->timeZone == ''){
                $userTimeZone = 'America/New_York';
            } else {
                $userTimeZone = $user->timeZone;
            }

            //Check for User's blog
            if(empty($user->blogId())){
                return response("User's Program not found", 401);
            }

            //providers
            $providers = WpBlog::getProviders($user->blogId());
            $nonCCMCareCenterUsers = WpBlog::getNonCCMCareCenterUsers($user->blogId());
            $careCenterUsers = WpBlog::getCareCenterUsers($user->blogId());
            $provider_info = array();

            if(!empty($providers)) {
                foreach ($providers as $provider) {
                    $provider_info[$provider->ID] = User::find($provider->ID)->getFullNameAttribute();
                }
            }
            if(!empty($nonCCMCareCenterUsers)) {
                foreach ($nonCCMCareCenterUsers as $nonCCMCareCenterUser) {
                    $provider_info[$nonCCMCareCenterUser->ID] = User::find($nonCCMCareCenterUser->ID)->getFullNameAttribute();
                }
            }
            debug($careCenterUsers);
            if(!empty($careCenterUsers)) {
                foreach ($careCenterUsers as $careCenterUser) {
                    $provider_info[$careCenterUser->ID] = User::find($careCenterUser->ID)->getFullNameAttribute();
                }
            }

            //Add care center users to Performed By Drop Down
            if(!empty($careteam_info)){
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
                'provider_info' => $provider_info,
                'careteam_info' => $careteam_info,
                'userTimeZone' => $userTimeZone
            ];

            return view('wpUsers.patient.note.create', $view_data);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $input, $patientId)
    {
        // convert minutes to seconds.
        if ($input['duration']) {
            $input['duration'] = $input['duration'] * 60;
        }
        $input = $input->all();
        $activity_id = Activity::createNewActivity($input);

        $admitted_flag = false;

        // store meta
        if (array_key_exists('meta', $input)) {
            $meta = $input['meta'];
            unset($input['meta']);
            $activity = Activity::find($activity_id);
            $metaArray = [];
            $i = 0;
            foreach ($meta as $actMeta) {
                if (isset($actMeta['meta_value'])) {
                    if ($actMeta['meta_value'] == 'admitted') {
                        $admitted_flag = true;
                    }
                    $metaArray[$i] = new ActivityMeta($actMeta);
                    $i++;
                }
            }
            $activity->meta()->saveMany($metaArray);
        }

        // update usermeta: cur_month_activity_time
        $activityService = new ActivityService;
        $activityService->reprocessMonthlyActivityTime($input['patient_id']);

        $activitySer = new ActivityService;
        $activity = Activity::find($activity_id);
        $linkToNote = URL::route('patient.note.view', array('patientId' => $patientId)) . '/' . $activity->id;
        $logger = User::find($input['logger_id']);
        $logger_name = $logger->display_name;

        //if emails are to be sent
        debug($input);
        if (array_key_exists('careteam', $input)) {
            //Log to Meta Table
            $noteMeta[] = new ActivityMeta(['meta_key' => 'email_sent_by', 'meta_value' => $logger->ID]);
            $noteMeta[] = new ActivityMeta(['meta_key' => 'email_sent_to', 'meta_value' => implode(", ", $input['careteam'])]);
            $activity->meta()->saveMany($noteMeta);

            $result = $activitySer->sendNoteToCareTeam($input['careteam'], $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true, $admitted_flag);

            if ($result) {
                return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Successfully Created And Note Sent']);
            } else return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Unable To Send Emails.']);

        } else if($admitted_flag){
            $u = User::find($patientId);
            $user_care_team = $u->sendAlertTo;
            $result = $activitySer->sendNoteToCareTeam($user_care_team, $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true, $admitted_flag);
        }
        return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Successfully Created Note']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Request $input, $patientId, $noteId)
    {
        $patient = User::find($patientId);
        $note_act = Activity::find($noteId);
        $metaComment = $note_act->getActivityCommentFromMeta($noteId);
        //$meta = DB::table('lv_activitymeta')->where('activity_id', $noteId)->where('meta_key', 'phone')->pluck('meta_value');
        $meta = $note_act->meta()->get();
        //dd($meta);

        //Set up note packet for view
        $note = array();

        //Sets up tags for patient note tags
        $meta_tags = array();
        foreach ($meta as $m) {
            if ($m->meta_key != 'comment') {
                switch ($m->meta_value) {
                    case('inbound'):
                        $meta_tags[] = 'Inbound Call';
                        break;
                    case('outbound'):
                        $meta_tags[] = 'Outbound Call';
                        break;
                    case('reached'):
                        $meta_tags[] = 'Patient Reached';
                        break;
                    case('admitted'):
                        $meta_tags[] = 'Patient Recently in Hospital/ER';
                        break;
                }
            }
        }
        //dd($meta_tags);

        $note['type'] = $note_act->type;
        $note['id'] = $note_act->id;
        $note['performed_at'] = $note_act->performed_at;
        $provider = User::find($note_act->provider_id);
        if ($provider) {
            $note['provider_name'] = $provider->getFullNameAttribute();
        } else {
            $note['provider_name'] = '';
        }

        $note['comment'] = $metaComment;

        $careteam_info = array();
        $careteam_ids = $patient->careTeam;
        if ((@unserialize($careteam_ids) !== false)) {
            $careteam_ids = unserialize($careteam_ids);
        }
        if(!empty($careteam_ids) && is_array($careteam_ids)) {
            foreach ($careteam_ids as $id) {
                $careteam_info[$id] = User::find($id) ? User::find($id)->getFullNameAttribute() : '';
            }
        }

        asort($careteam_info);

        $view_data = ['note' => $note, 'userTimeZone' => $patient->timeZone, 'careteam_info' => $careteam_info, 'patient' => $patient, 'program_id' => $patient->blogId(), 'meta' => $meta_tags];

        return view('wpUsers.patient.note.view', $view_data);
    }


    public function send(Request $input, $patientId, $noteId)
    {
        $input = $input->all();
        debug($input);

        if (isset($input['careteam'])) {
            $activity = Activity::findOrFail($input['noteId']);
            $activityService = new ActivityService;
            $logger = User::find($input['logger_id']);
            $logger_name = $logger->getFullNameAttribute();
            $linkToNote = URL::route('patient.note.view', array('patientId' => $patientId)) . '/' . $activity->id;

            $noteMeta[] = new ActivityMeta(['meta_key' => 'email_sent_by', 'meta_value' => $logger->ID]);
            $noteMeta[] = new ActivityMeta(['meta_key' => 'email_sent_to', 'meta_value' => implode(", ", $input['careteam'])]);
            $activity->meta()->saveMany($noteMeta);

            $result = $activityService->sendNoteToCareTeam($input['careteam'], $linkToNote, $activity->performed_at, $input['patient_id'], $logger_name, false);

            return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Note Successfully Sent!']);
        }
        return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Something went wrong...']);
    }
}

