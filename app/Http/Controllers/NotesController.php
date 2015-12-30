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

        if (isset($input['selectMonth'])) {
            $time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
            $start = $time->startOfMonth()->format('Y-m-d');
            $end = $time->endOfMonth()->format('Y-m-d');
            $month_selected = $time->format('m');
        } else {
            $time = Carbon::now();
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');
            $month_selected = $time->format('m');
        }

        $acts = DB::table('activities')
            ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration)'))
            ->whereBetween('performed_at', [
                $start, $end
            ])
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

//			$reportData[$patientId] = array();
        $reportData[$patientId] = array();
        foreach ($activities_data_with_users as $patientAct) {
            //debug($patientAct);
            $reportData[] = collect($patientAct)->groupBy('performed_at_year_month');
            //$reportData[$patientAct[0]['patient_id']]getActivityCommentFromMeta($id)
        }
//        foreach ($reportData as $user_id => $date_month) {
//            foreach ($date_month as $month => $user_activities) {
//                $i = 0;
//                foreach ($user_activities as $user_activity) {
//
//                    $activity_json[$i] = $user_activity;
//
//                    $activity_json[$i]['comment'] = $user_activity['comment'];
//
//                    // logger details
//                    $logger_user = User::find($user_activity['logger_id']);
//                    $logger_name = $logger_user->getFullNameAttribute();
//                    $activity_json[$i]['logger_name'] = $logger_name;
//
//                    // provider details
//                    if ($user_activity['provider_id'] == $user_activity['logger_id']) {
//                        $activity_json[$i]['provider_name'] = $logger_name;
//                    } else {
//                        $provider_user = User::find($user_activity['provider_id']);
//                        $provider_name = $provider_user->getFullNameAttribute();
//                        $activity_json[$i]['provider_name'] = $provider_name;
//                    }
//
//                    // Type
//                    if ($user_activity['logged_from'] == 'note') {
//                        $activity_json[$i]['note_type'] = 'Note';
//                    } else {
//                        $activity_json[$i]['note_type'] = 'Offline Activity';
//                    }
//
//                    // date format
//                    if ($user_activity['logged_from'] == 'manual_input') {
//                        $activity_json[$i]['performed_at_date'] = date('m-d-y h:i:s A', strtotime($user_activity['performed_at']));
//                    } else {
//                        $activity_json[$i]['performed_at_date'] = date('m-d-y', strtotime($user_activity['performed_at']));
//                    }
//
//                    // type name
//                    foreach (Activity::input_activity_types() as $abbrev => $activity_type) {
//                        if ($user_activity['type'] == $abbrev) {
//                            $activity_json[$i]['type_name'] = $user_activity['type'];
//                            // echo '<pre>'; var_export($activity_json[$i]['logged_from']);echo '</pre>';
//                        }
//                    }
//                    $i++;
//                }
//            }
//        }
        for($i = 0; $i < count($patientAct) - 1; $i++){
            $logger_user = User::find($patientAct[$i]['logger_id']);
            if($logger_user){
            $patientAct[$i]['logger_name'] = $logger_user->getFullNameAttribute();
            } else {
                $patientAct[$i]['logger_name'] = 'N/A';
            }
        }
        $data = true;
        $reportData = "data:" . json_encode($patientAct) . "";
        if($patientAct == null){
            $data = false;
        }


        $years = array();
        for ($i = 0; $i < 3; $i++) {
            $years[] = Carbon::now()->subYear($i)->year;
        }

        $months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');


        return view('wpUsers.patient.note.index',
            ['activity_json' => $reportData,
                'years' => array_reverse($years),
                'month_selected' => $month_selected,
                'months' => $months,
                'patient' => $patient,
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
            $linkToNote = $input['url'] . $activity->id;
            $logger = User::find($input['logger_id']);
            $logger_name = $logger->display_name;

            $result = $activitySer->sendNoteToCareTeam($input['careteam'], $linkToNote, $input['performed_at'], $input['patient_id'], $logger_name, true);

            if ($result) {
                return response("Successfully Created And Note Sent", 202);
            } else return response("Unable to send emails", 401);

        } else return response("Successfully Created", 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //
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

    public function sendExistingNote($activity_id, $logger_id, $url, $careteam)
    {
            $activity = Activity::findOrFail($activity_id);
            $activityService = new ActivityService;
            $logger = User::find($logger_id);
            $logger_name = $logger->display_name;
            $linkToNote = $url.$activity->id;
            $result = $activityService->sendNoteToCareTeam($careteam,$linkToNote,$activity->performed_at,$input['patient_id'],$logger_name, false);
            if ($result) {
                return response("Successfully Sent", 202);
            } else {
                return response("Sorry, could not sent Note!", 401);
            }
    }

}
