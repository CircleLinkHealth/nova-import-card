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

/** @todo Move Store and Send note functions from Activity Controller to here */
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
        $input = $request->all();
        $messages = \Session::get('messages');

        $acts = DB::table('lv_activities')
            ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration)'))
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->where('logged_from', 'note')
                    ->Orwhere('logged_from', 'manual_input');
            })
            ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
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
            $wpUser = User::find($patientId);
            if (!$wpUser) {
                return response("User not found", 401);
            }

            $patient_name = $wpUser->getFullNameAttribute();

            //Gather details to generate form

            //timezone


            //careteam
            $careteam_info = array();
            $careteam_ids = $wpUser->careTeam;
            foreach ($careteam_ids as $id) {
                $careteam_info[$id] = User::find($id)->getFullNameAttribute();;
            }

            //providers
            $providers = WpBlog::getProviders($wpUser->blogId());
            $provider_info = array();

            foreach ($providers as $provider) {
                $provider_info[$provider->ID] = User::find($provider->ID)->getFullNameAttribute();
            }

            $view_data = [
                'program_id' => $wpUser->blogId(),
                'patient' => $wpUser,
                'patient_name' => $patient_name,
                'note_types' => Activity::input_activity_types(),
                'provider_info' => $provider_info,
                'careteam_info' => $careteam_info,
                'userTimeZone' => $wpUser->timeZone
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

        // store meta
        if (array_key_exists('meta', $input)) {
            $meta = $input['meta'];
            unset($input['meta']);
            $activity = Activity::find($activity_id);
            $metaArray = [];
            $i = 0;
            foreach ($meta as $actMeta) {
                $metaArray[$i] = new ActivityMeta($actMeta);
                $i++;
            }
            $activity->meta()->saveMany($metaArray);
        }

        // update usermeta: cur_month_activity_time
        $activityService = new ActivityService;
        $activityService->reprocessMonthlyActivityTime($input['patient_id']);
        //if alerts are to be sent
        if (array_key_exists('careteam', $input)) {
            $activitySer = new ActivityService;
            $activity = Activity::find($activity_id);
            $linkToNote = URL::route('patient.note.view', array('patientId' => $patientId)) . '/' . $activity->id;
            $logger = User::find($input['logger_id']);
            $logger_name = $logger->display_name;

            $result = $activitySer->sendNoteToCareTeam($input['careteam'], $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true);

            if ($result) {
                return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Successfully Created And Note Sent']);
            } else return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Unable To Send Emails.']);

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
        $phone = DB::table('lv_activitymeta')->where('activity_id', $noteId)->where('meta_key', 'phone')->pluck('meta_value');

        //Set up note pack for view
        $note = array();
        if ($phone) {
            $note['phone'] = $phone;
        }

        $note['type'] = $note_act->type;
        $note['id'] = $note_act->id;
        $note['performed_at'] = $note_act->performed_at;
        $note['provider_name'] = (User::find($note_act->provider_id)->getFullNameAttribute());
        $note['comment'] = $metaComment;

        $careteam_info = array();
        $careteam_ids = $patient->careTeam;
        foreach ($careteam_ids as $id) {
            $careteam_info[$id] = User::find($id)->getFullNameAttribute();;
        }

        $view_data = ['note' => $note, 'userTimeZone' => $patient->timeZone, 'careteam_info' => $careteam_info, 'patient' => $patient, 'program_id' => $patient->blogId()];

        debug($note);
        return view('wpUsers.patient.note.view', $view_data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function send(Request $input, $patientId, $noteId)
    {
        debug($input->all());
        $input = $input->all();

        if (isset($input['careteam'])) {
            $activity = Activity::findOrFail($input['noteId']);
            $activityService = new ActivityService;
            $logger = User::find($input['logger_id']);
            $logger_name = $logger->getFullNameAttribute();
            $linkToNote = URL::route('patient.note.view', array('patientId' => $patientId)) . '/' . $activity->id;

            $result = $activityService->sendNoteToCareTeam($input['careteam'], $linkToNote, $activity->performed_at, $input['patient_id'], $logger_name, false);

            return redirect()->route('patient.note.index', ['patient' => $patientId])->with('messages', ['Note Successfully Sent!']);
        }
            return redirect()->route('patient.note.index', ['patient' => $patientId]);
    }
}

