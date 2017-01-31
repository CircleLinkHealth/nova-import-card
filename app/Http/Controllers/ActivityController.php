<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Practice;
use App\Reports\PatientDailyAuditReport;
use App\Services\ActivityService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Class ActivityController
 * @package App\Http\Controllers
 */
class ActivityController extends Controller
{

	public function providerUIIndex(
		Request $request,
		$patientId
	) {

		$patient = User::find($patientId);

		$input = $request->all();

		$messages = \Session::get('messages');

		if (isset($input['selectMonth'])) {

			$time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
			$start = $time->startOfMonth()->format('Y-m-d H:i:s');
			$end = $time->endOfMonth()->format('Y-m-d H:i:s');
			$month_selected = $time->format('m');
			$month_selected_text = $time->format('F');
			$year_selected = $time->format('Y');

		} else {

			$time = Carbon::now();
			$start = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
			$end = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
			$month_selected = $time->format('m');
			$month_selected_text = $time->format('F');
			$year_selected = $time->format('Y');

		}

		//downloads patient audit
		if ($request->ajax()) {

			$data = (new PatientDailyAuditReport($patient->patientInfo, Carbon::parse($start)))->renderPDF();

			return $data;

		}

		$acts = DB::table('lv_activities')
//			->select(DB::raw('id,provider_id,logged_from,DATE(performed_at)as performed_at, type, SUM(duration) as duration'))
			->where('created_at', '>=', $start)
			->where('created_at', '<=', $end)
			->where('patient_id', $patientId)
			->where(function ($q) {
				$q->where('logged_from', 'activity')
					->Orwhere('logged_from', 'manual_input')
					->Orwhere('logged_from', 'pagetimer');
			})
			->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
			->orderBy('created_at', 'desc')
			->get();


		$acts = json_decode(json_encode($acts), true);            //debug($acts);


		foreach ($acts as $key => $value) {
			$provider = User::find($acts[$key]['provider_id']);
			if ($provider) {
				$acts[$key]['provider_name'] = $provider->getFullNameAttribute();
			}
			unset($acts[$key]['provider_id']);
		}
		if ($acts) {
			$data = true;
		} else {
			$data = false;
		}

		$reportData = "data:" . json_encode($acts) . "";

		$years = [];
		for ($i = 0; $i < 3; $i++) {
			$years[] = Carbon::now()->subYear($i)->year;
		}

		$months = [
			'Jan',
			'Feb',
			'Mar',
			'Apr',
			'May',
			'Jun',
			'Jul',
			'Aug',
			'Sep',
			'Oct',
			'Nov',
			'Dec',
		];

		return view('wpUsers.patient.activity.index',
			[
				'activity_json'       => $reportData,
				'years'               => array_reverse($years),
				'month_selected'      => $month_selected,
				'month_selected_text' => $month_selected_text,
				'year_selected'       => $year_selected,
				'months'              => $months,
				'patient'             => $patient,
				'data'                => $data,
				'messages'            => $messages,
			]);
	}

	public function index(Request $request)
	{
		// display view
		$activities = Activity::orderBy('id', 'desc')->paginate(10);

		return view('activities.index', ['activities' => $activities]);

	}

	public function create(
		Request $request,
		$patientId
	) {
		if (auth()->user()->hasRole('care-center') && app()->environment() == 'production') {

			return abort(403);

		}

		if ($patientId) {
			// patient view
			$user = User::find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}

			$patient_name = $user->getFullNameAttribute();

			//Gather details to generate form

			//timezone

			if ($user->timeZone == '') {
				$userTimeZone = 'America/New_York';
			} else {
				$userTimeZone = $user->timeZone;
			}

			//careteam
			$careteam_info = [];
			$careteam_ids = $user->careTeam;
			if ((@unserialize($careteam_ids) !== false)) {
				$careteam_ids = unserialize($careteam_ids);
			}
			if (!empty($careteam_ids) && is_array($careteam_ids)) {
				foreach ($careteam_ids as $id) {
					$careteam_info[$id] = User::find($id)->getFullNameAttribute();
				}
			}

			//providers
			$providers = Practice::getProviders($user->program_id);
			$provider_info = [];

			$nurse_ids = User::ofType('care-center')
				->pluck('id');

			foreach ($nurse_ids as $nurse_id) {

				$nurse = User::find($nurse_id);

				$viewable_patients = $nurse->viewablePatientIds();

				if (in_array($patientId, $viewable_patients)) {
					$provider_info[$nurse->id] = $nurse->fullName;
				}

			}


			foreach ($providers as $provider) {
				$provider_info[$provider->id] = User::find($provider->id)->getFullNameAttribute();
			}

			foreach ($providers as $provider) {
				$provider_info[$provider->id] = User::find($provider->id)->getFullNameAttribute();
			}


			asort($provider_info);

			$view_data = [
				'program_id'     => $user->program_id,
				'patient'        => $user,
				'patient_name'   => $patient_name,
				'activity_types' => Activity::input_activity_types(),
				'provider_info'  => $provider_info,
				'careteam_info'  => $careteam_info,
				'userTimeZone'   => $userTimeZone,
			];

			return view('wpUsers.patient.activity.create', $view_data);
		}
	}

	public function store(
		Request $request,
		$params = false
	) {

		if ($params) {
			$input = $request->all();
		} else {
			if ($request->isJson()) {
				$input = $request->input();
			} else {
				return response("Unauthorized", 401);
			}
		}//debug($request->all());

		// convert minutes to seconds.
		if ($input['duration']) {
			$input['duration'] = $input['duration'] * 60;
		}

		// store activity
		$actId = Activity::createNewActivity($input);

		$activity = Activity::find($actId);
		$nurse = User::find($activity->provider_id)->nurseInfo;

		// store meta
		if (array_key_exists('meta', $input)) {
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

		if ($nurse) {

			$activity = Activity::find($actId);

			$computer = new AlternativeCareTimePayableCalculator($nurse);
			$computer->adjustCCMPaybleForActivity($activity);

		}

		return redirect()->route('patient.activity.view', [
			'patient' => $activity->patient_id,
			'actId'   => $activity->id,
		])->with('messages',

			['Successfully Created New Offline Activity']

		);
	}

	public function show(
		Request $input,
		$patientId,
		$actId
	) {
		$patient = User::find($patientId);
		$act = Activity::find($actId);
		//Set up note pack for view
		$activity = [];
		$messages = \Session::get('messages');
		$activity['type'] = $act->type;
		$activity['performed_at'] = $act->performed_at;
		$activity['provider_name'] = User::find($act->provider_id)
			? (User::find($act->provider_id)->getFullNameAttribute())
			: '';
		$activity['duration'] = intval($act->duration) / 60;

		$careteam_info = [];
		$careteam_ids = $patient->careTeam;
		if ((@unserialize($careteam_ids) !== false)) {
			$careteam_ids = unserialize($careteam_ids);
		}
		if (!empty($careteam_ids) && is_array($careteam_ids)) {
			foreach ($careteam_ids as $id) {
				$careteam_info[$id] = User::find($id)->getFullNameAttribute();;
			}
		}

		$comment = $act->getActivityCommentFromMeta($actId);
		if ($comment) {
			$activity['comment'] = $comment;
		} else {
			$activity['comment'] = '';
		}

		$view_data = [
			'activity'      => $activity,
			'userTimeZone'  => $patient->timeZone,
			'careteam_info' => $careteam_info,
			'patient'       => $patient,
			'program_id'    => $patient->program_id,
			'messages'      => $messages,
		];

		return view('wpUsers.patient.activity.view', $view_data);
	}

	public function update(Request $request)
	{
		if ($request->isJson()) {
			$input = $request->input();
		} else {
			if ($request->isMethod('POST')) {
				if ($request->header('Client') == 'ui') // WP Site
				{
					$input = json_decode(Crypt::decrypt($request->input('data')), true);
				}
			} else {
				return response("Unauthorized", 401);
			}
		}

		//  Check if there are any meta nested parts in the incoming request
		$meta = $input['meta'];
		unset($input['meta']);

		$activity = Activity::find($input['activity_id']);
		$activity->fill($input)->save();

		$actMeta = ActivityMeta::where('activity_id', $input['activity_id'])->where('meta_key',
			$meta['0']['meta_key'])->first();
		$actMeta->fill($meta['0'])->save();

		$activityService = new ActivityService;
		$result = $activityService->reprocessMonthlyActivityTime($input['patient_id']);

		return response("Activity Updated", 201);
	}

	public function destroy($id)
	{
		//
	}
}
