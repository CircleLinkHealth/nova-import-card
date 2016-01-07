<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\WpBlog;
use App\User;
use App\UserMeta;
use App\Services\ActivityService;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Class ActivityController
 * @package App\Http\Controllers
 */
class ActivityController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
	public function index(Request $request)
	{
			// display view
			$activities = Activity::orderBy('id', 'desc')->paginate(10);
			return view('activities.index', [ 'activities' => $activities ]);

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
				'activity_types' => Activity::input_activity_types(),
				'provider_info' => $provider_info,
				'careteam_info' => $careteam_info,
				'userTimeZone' => $wpUser->timeZone
			];

			return view('wpUsers.patient.activity.create', $view_data);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  array  $params
	 * @return Response
	 */
	public function store(Request $request, $params = false)
	{

        if($params) {
			$input = $request->all();
		} else if ( $request->isJson() ) {
			$input = $request->input();
		} else {
			return response("Unauthorized", 401);
		}

		debug($request->all());

        // convert minutes to seconds.
		if($input['duration']) {
			$input['duration'] = $input['duration'] * 60;
		}

		// store activity
		$actId = Activity::createNewActivity($input);

		// store meta
		if (array_key_exists('meta',$input)) {
			$meta = $input['meta'];
			unset($input['meta']);
			$activity = Activity::find($actId);
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

		return redirect()->route('patient.activity.view', ['patient' => $activity->patient_id, 'actId' => $activity->id])->with('messages', ['Successfully Created New Offline Activity']);
	}



	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $input, $patientId, $actId)
	{
		$patient = User::find($patientId);
		$act = Activity::find($actId);
		//Set up note pack for view
		$activity = array();
		$messages = \Session::get('messages');
		$activity['type'] = $act->type;
		$activity['performed_at'] = $act->performed_at;
		$activity['provider_name'] = (User::find($act->provider_id)->getFullNameAttribute());
		$activity['duration'] = intval($act->duration)/60;

		$careteam_info = array();
		$careteam_ids = $patient->careTeam;
		foreach ($careteam_ids as $id) {
			$careteam_info[$id] = User::find($id)->getFullNameAttribute();;
		}

		$comment = $act->getActivityCommentFromMeta($actId);
		if($comment){
			$activity['comment'] = $comment;
		} else {
			$activity['comment'] = '';
		}

		$view_data = ['activity' => $activity, 'userTimeZone' => $patient->timeZone,'careteam_info' => $careteam_info, 'patient' => $patient,'program_id' => $patient->blogId(), 'messages' => $messages];

		debug($activity);
		return view('wpUsers.patient.activity.view', $view_data);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Response
     */
	public function update(Request $request)
	{
		if ( $request->isJson() )
		{
			$input = $request->input();
		}
		else if ( $request->isMethod('POST') )
		{
			if ( $request->header('Client') == 'ui' ) // WP Site
			{
				$input = json_decode(Crypt::decrypt($request->input('data')), true);
			}
		}
		else
		{
			return response("Unauthorized", 401);
		}

		//  Check if there are any meta nested parts in the incoming request
		$meta = $input['meta'];
		unset($input['meta']);

		$activity = Activity::find($input['activity_id']);
		$activity->fill($input)->save();

		$actMeta = ActivityMeta::where('activity_id', $input['activity_id'])->where('meta_key',$meta['0']['meta_key'])->first();
		$actMeta->fill($meta['0'])->save();

		$activityService = new ActivityService;
		$result = $activityService->reprocessMonthlyActivityTime($input['patient_id']);

		return response("Activity Updated", 201);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function providerUIIndex(Request $request, $patientId)
	{		debug($patientId);


		$patient = User::find($patientId);
		$input = $request->all();
		$messages = \Session::get('messages');
		debug($messages);
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
			->select(DB::raw('id,provider_id,logged_from,DATE(performed_at), type, duration'))
			->whereBetween('performed_at', [
				$start, $end
			])
			->where('patient_id', $patientId)
			->where(function ($q) {
				$q->where('logged_from', 'activity')
					->Orwhere('logged_from', 'manual_input');
			})
			->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
			->orderBy('performed_at', 'desc')
			->get();

		$acts = json_decode(json_encode($acts), true);

		foreach ($acts as $key => $value) {
			$provider = User::find($acts[$key]['provider_id']);
			$acts[$key]['provider_name'] = $provider->getFullNameAttribute();
			unset($acts[$key]['provider_id']);
		}

		if ($acts) {$data = true;} else {$data = false;}

		$reportData = "data:" . json_encode($acts) . "";

		$years = array();
		for ($i = 0; $i < 3; $i++) {
			$years[] = Carbon::now()->subYear($i)->year;
		}

		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		debug($reportData);
		return view('wpUsers.patient.activity.index',
			['activity_json' => $reportData,
				'years' => array_reverse($years),
				'month_selected' => $month_selected,
				'months' => $months,
				'patient' => $patient,
				'data' => $data,
				'messages' => $messages
			]);
		}
}
