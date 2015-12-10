<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Services\ActivityService;
use App\WpBlog;
use App\WpUser;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @todo Move Store and Send note functions from Activity Controller to here */

class NotesController extends Controller {

	/**
	 * Display a listing of the notes for a user.
	 *
	 * @return Response
	 */
	public function index(Request $request, $patientId)
	{
		$view_data = array();
		return view('wpUsers.patient.note.index',$view_data);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request, $patientId)
	{

		if($patientId) {
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
			foreach ($careteam_ids as $id){
				$careteam_info[$id] = WpUser::find($id)->getFullNameAttribute();;
			}

			//providers
			$providers = WpBlog::getProviders($wpUser->blogId());
			$provider_info = array();

			foreach ($providers as $provider){
				$provider_info[$provider->ID] = WpUser::find($provider->ID)->getFullNameAttribute();
			}

			$view_data = [
				'program_id' => $wpUser->blogId(),
				'patient' => $wpUser,
				'patient_name' => $patient_name,
				'note_types' => Activity::input_activity_types(),
				'provider_info' => $provider_info,
				'careteam_info' => $careteam_info,
				'userTimeZone' => $wpUser->getUserTimeZone()
			];

			return view('wpUsers.patient.note.create',$view_data);
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
		if($input['duration']) {
			$input['duration'] = $input['duration'] * 60;
		}

		$input = $input->all();


		$activity_id = Activity::createNewActivity($input);

		// store meta
		if (array_key_exists('meta',$input)) {
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
		if (array_key_exists('careteam',$input)) {
			$activitySer = new ActivityService;
			$activity = Activity::find($activity_id);
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
	public function show($id)
	{
		//
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
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
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
