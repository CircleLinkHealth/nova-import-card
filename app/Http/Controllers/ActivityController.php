<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\WpBlog;
use App\WpUser;
use App\WpUserMeta;
use App\Services\ActivityService;
use App\Http\Requests;
use App\Http\Controllers\Controller;

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
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}

			$patient_name = $wpUser->getFullNameAttribute();

			//Gather details to generate form

			//timezone

			//careteam
			$careteam_info = array();
			$careteam_ids = $wpUser->getCareTeamIDs();
			foreach ($careteam_ids as $id) {
				$careteam_info[$id] = WpUser::find($id)->getFullNameAttribute();;
			}

			//providers
			$providers = WpBlog::getProviders($wpUser->blogId());
			$provider_info = array();

			foreach ($providers as $provider) {
				$provider_info[$provider->ID] = WpUser::find($provider->ID)->getFullNameAttribute();
			}

			$view_data = [
				'program_id' => $wpUser->blogId(),
				'patient' => $wpUser,
				'patient_name' => $patient_name,
				'activity_types' => Activity::input_activity_types(),
				'provider_info' => $provider_info,
				'careteam_info' => $careteam_info,
				'userTimeZone' => $wpUser->getUserTimeZone()
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

		//if alerts are to be sent
		if (array_key_exists('careteam',$input)) {
			$activitySer = new ActivityService;
			$activity = Activity::find($actId);
			$linkToNote = $input['url'].$activity->id;
			$logger = WpUser::find($input['logger_id']);
			$logger_name = $logger->display_name;

			$result = $activitySer->sendNoteToCareTeam($input['careteam'],$linkToNote,$input['performed_at'],$input['patient_id'],$logger_name, true);

			if($result)
			{
				return response("Successfully Created And Note Sent", 202);
			}
			else return  response("Unable to send emails", 401);

		} else return response("Successfully Created", 201);

	}



	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request, $id)
	{
		if ( $request->header('Client') == 'ui' ) // WP Site
		{
			$activity = Activity::findOrFail($id);

			//extract and attach the 'comment' value from the ActivityMeta table
			$metaComment = $activity->getActivityCommentFromMeta($id);

			//If it's a note, search for phone meta value
			$phone = DB::table('activitymeta')->where('activity_id',$id)->where('meta_key','phone')->pluck('meta_value');
			if($phone){
				$activity['phone'] = $phone;
			}

			$activity['comment'] = $metaComment;
			$activity['message'] = 'OK';
			$json = Array();
			$json['body'] = $activity;
			$json['message'] = 'OK';
			return response(Crypt::encrypt(json_encode($json)));
		} else {
			$activity = Activity::find($id);
			if($activity) {
				return view('activities.show', ['activity' => $activity]);
			} else {
				return response("Activity not found", 401);
			}
        }
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

}
