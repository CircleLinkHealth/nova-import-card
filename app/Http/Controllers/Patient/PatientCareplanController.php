<?php namespace App\Http\Controllers\Patient;

use App\Activity;
use App\Observation;
use App\WpBlog;
use App\Location;
use App\WpUser;
use App\WpUserMeta;
use App\CPRulesPCP;
use App\Role;
use App\Services\CareplanUIService;
use App\CLH\Repositories\WpUserRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;
use PasswordHash;
use Symfony\Component\HttpFoundation\ParameterBag;
use Auth;
use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PatientCareplanController extends Controller {

	/**
	 * Display patient add/edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientDemographics(Request $request, $patientId = false)
	{
		$messages = \Session::get('messages');

		// determine if existing user or new user
		$user = new WpUser;
		if($patientId) {
			$user = WpUser::find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
		}
		$patient = $user;

		// get program
		$programId = \Session::get('activeProgramId');

		// roles
		$patientRoleId = Role::where('name', '=', 'patient')->first();
		$patientRoleId = $patientRoleId->id;

		// user meta
		$userMeta = $user->userMetaTemplate();
		if($patientId) {
			$userMeta = WpUserMeta::where('user_id', '=', $patientId)->lists('meta_value', 'meta_key');
		}

		// user config
		$userConfig = $user->userConfigTemplate();
		if($patientId) {
			if (isset($userMeta['wp_' . $programId . '_user_config'])) {
				$userConfig = unserialize($userMeta['wp_' . $programId . '_user_config']);
				$userConfig = array_merge($user->userConfigTemplate(), $userConfig);
			}
			// set role
			$capabilities = unserialize($userMeta['wp_' . $programId . '_capabilities']);
			$wpRole = key($capabilities);
		}

		// locations @todo get location id for WpBlog
		$wpBlog = WpBlog::find($programId);
		$locations = Location::where('program_id', '=', $programId)->lists('name', 'id');

		// States (for dropdown)
		$states = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");

		// timezones for dd
		$timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($timezones_raw as $timezone) {
			$timezones[$timezone] = $timezone;
		}

		return view('wpUsers.patient.careplan.patient', compact(['patient', 'userMeta', 'userConfig','states', 'locations', 'timezones', 'messages', 'patientRoleId']));
	}


	/**
	 * Save patient add/edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function storePatientDemographics(Request $request)
	{
		// input
		$params = new ParameterBag($request->input());
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$user = WpUser::with('meta')->find($patientId);
		if (!$user) {
			return response("User not found", 401);
		}

		$userRepo = new WpUserRepository();

		$userRepo->editUser($user, $params);

		if($params->get('direction')) {
			return redirect($params->get('direction'));
		}
		return redirect()->back()->with('messages', ['successfully updated user']);
	}







	/**
	 * Display patient careteam edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientCareteam(Request $request, $patientId = false)
	{
		$messages = \Session::get('messages');

		$wpUser = false;
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}
		$patient = $wpUser;

		// program
		$program = WpBlog::find($wpUser->program_id);

		$params = $request->all();

		// user config
		$userConfig = $wpUser->userConfigTemplate();
		if(isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
			$userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
			$userConfig = array_merge($wpUser->userConfigTemplate(), $userConfig);
		}

		//$sectionHtml = $carePlanUI->renderCareplanSection($wpUser, 'Biometrics to Monitor');
		$sectionHtml = (new CareplanUIService)->renderCareplanSections(array(), $wpUser->program_id, $wpUser);
		//$sectionHtml = '';
		//dd($sectionHtml);

		//dd($userConfig);

		return view('wpUsers.patient.careplan.careteam', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml']));
	}


	/**
	 * Save patient careteam edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function storePatientCareteam(Request $request)
	{
		// input
		$params = new ParameterBag($request->input());
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$wpUser = WpUser::with('meta')->find($patientId);
		if (!$wpUser) {
			return response("User not found", 401);
		}

		if($params->get('direction')) {
			return redirect($params->get('direction'));
		}
		return redirect()->back()->with('messages', ['successfully updated user']);

		//return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
	}













	/**
	 * Display patient careteam edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientCareplan(Request $request, $patientId = false)
	{
		$messages = \Session::get('messages');

		$wpUser = false;
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}
		$patient = $wpUser;

		// program
		$program = WpBlog::find($wpUser->program_id);

		$params = $request->all();

		// user config
		$userConfig = $wpUser->userConfigTemplate();
		if(isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
			$userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
			$userConfig = array_merge($wpUser->userConfigTemplate(), $userConfig);
		}

		//$sectionHtml = $carePlanUI->renderCareplanSection($wpUser, 'Biometrics to Monitor');
		$sectionHtml = (new CareplanUIService)->renderCareplanSections(array(), $wpUser->program_id, $wpUser);

		return view('wpUsers.patient.careplan.careplan', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml']));
	}


	/**
	 * Save patient careteam edit
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function storePatientCareplan(Request $request)
	{
		// input
		$params = new ParameterBag($request->input());
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$wpUser = WpUser::with('meta')->find($patientId);
		if (!$wpUser) {
			return response("User not found", 401);
		}

		if($params->get('direction')) {
			return redirect($params->get('direction'));
		}
		return redirect()->back()->with('messages', ['successfully updated patient careplan']);

		//return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
	}






	/**
	 * Display Careplan Print
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientCareplanPrint(Request $request, $patientId = false)
	{
		$wpUser = array();
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		// program
		$program = WpBlog::find($wpUser->program_id);

		return view('wpUsers.patient.careplan.print', ['program' => $program, 'patient' => $wpUser]);
	}
}
