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
		$programId = \Session::get('activeProgramId');

		// input
		$params = new ParameterBag($request->input());
		$patientId = false;
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$user = new WpUser;
		if($patientId) {
			$user = WpUser::with('meta')->find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
		}

		$userRepo = new WpUserRepository();

		if($patientId) {
			$userRepo->editUser($user, $params);
			if($params->get('direction')) {
				return redirect($params->get('direction'))->with('messages', ['Successfully updated patient demographics.']);
			}
			return redirect()->back()->with('messages', ['Successfully updated patient demographics.']);
		} else {
			$role = Role::whereName('patient')->first();

			$newUserId = str_random(20);

			$bag = new ParameterBag([
				'user_email' => $newUserId . '@careplanmanager.com',
				'user_pass' => 'whatToPutHere',
				'user_nicename' => $newUserId,
				'program_id' => $programId,
				'roles' => [$role->id],
			]);
			$newUser = $userRepo->createNewUser($user, $bag);
			$userRepo->editUser($newUser, $params);
			return redirect(\URL::route('patient.demographics.show', array('patientId' => $newUser->ID)))->with('messages', ['Successfully created new patient with demographics.']);
		}
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

		// get user config
		$userMeta = WpUserMeta::where('user_id', '=', $patientId)->lists('meta_value', 'meta_key');
		$userConfig = unserialize($userMeta['wp_' . $programId . '_user_config']);
		$userConfig = array_merge($user->userConfigTemplate(), $userConfig);

		// care team vars
		$careTeamUserIds = $userConfig['care_team'];
		$ctmsa = array();
		if(!empty($userConfig['send_alert_to'])) {
			$ctmsa = $userConfig['send_alert_to'];
		}
		$ctbp = $userConfig['billing_provider'];
		$ctlc = $userConfig['lead_contact'];

		//dd($userConfig);

		$careTeamUsers = array();
		foreach($careTeamUserIds as $id) {
			$careTeamUsers[] = WpUser::find($id);
		}

		// get providers
		$providersData = array();
		$providers = WpUser::where('program_id', '=', $programId)
			->with('meta')
			->whereHas('roles', function($q){
				$q->where('name', '=', 'provider');
			})->get();

		$phtml = '<div id="providerInfoContainers" style="">Hidden containers with provider info:';
		foreach ($providers as $provider) {
			$providersData[$provider->ID] = $provider->fullName;
			// meta
			$userMeta = WpUserMeta::where('user_id', '=', $provider->ID)->lists('meta_value', 'meta_key');

			// config
			$userConfig = $provider->userConfigTemplate();
			if (isset($userMeta['wp_' . $programId . '_user_config'])) {
				$userConfig = unserialize($userMeta['wp_' . $programId . '_user_config']);
				$userConfig = array_merge($user->userConfigTemplate(), $userConfig);
			}
			$phtml .= '<div id="providerInfo' . $provider->ID . '">';
			$phtml .= '<strong><span id="providerName' . $provider->ID . '" style="display:none;">' . ucwords($userMeta['first_name'] . ' ' . $userMeta['last_name']) . '</span></strong>';
			$phtml .= '<strong>Specialty:</strong> ' . $userConfig['specialty'];
			$phtml .= '<BR><strong>Tel:</strong> ' . $userConfig['study_phone_number'];
			$phtml .= '</div>';
		}
		$phtml .= '</div>';

		return view('wpUsers.patient.careplan.careteam', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml', 'phtml', 'providersData', 'careTeamUsers']));
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
			return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care team.']);
		}
		return redirect()->back()->with('messages', ['Successfully updated patient care team.']);

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
			return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care plan.']);
		}
		return redirect()->back()->with('messages', ['successfully updated patient care plan']);

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
