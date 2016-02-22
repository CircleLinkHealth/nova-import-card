<?php namespace App\Http\Controllers\Patient;

use App\Activity;
use App\CareSection;
use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\Observation;
use App\CarePlan;
use App\CarePlanItem;
use App\WpBlog;
use App\Location;
use App\User;
use App\UserMeta;
use App\CPRulesPCP;
use App\Role;
use App\Services\ReportsService;
use App\Services\CareplanUIService;
use App\CLH\Repositories\UserRepository;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;
use PasswordHash;
use Symfony\Component\HttpFoundation\ParameterBag;
use Input;
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
		$user = new User;
		$programId = false;
		if($patientId) {
			$user = User::find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
			$programId = $user->program_id;
		}
		$patient = $user;

		// security
		if(!Auth::user()->can('observations-view')) {
			abort(403);
		}

		// get program
		$programs = WpBlog::whereIn('blog_id', Auth::user()->viewableProgramIds())->lists('display_name', 'blog_id');

		// roles
		$patientRoleId = Role::where('name', '=', 'participant')->first();
		$patientRoleId = $patientRoleId->id;

		// user meta
		$userMeta = (new UserMetaTemplate())->getArray();
		if($patientId) {
			$userMeta = UserMeta::where('user_id', '=', $patientId)->lists('meta_value', 'meta_key');
		}

		// user config
		$userConfig = (new UserConfigTemplate())->getArray();
		if($patientId) {
			if (isset($userMeta['wp_' . $programId . '_user_config'])) {
				$userConfig = unserialize($userMeta['wp_' . $programId . '_user_config']);
				$userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
			}
			// set role
			$capabilities = array();
			if(isset($userMeta['wp_' . $programId . '_capabilities'])) {
				$capabilities = unserialize($userMeta['wp_' . $programId . '_capabilities']);
				$wpRole = key($capabilities);
			}
		}

		// get careplan
		$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
			->first();

		if(!$carePlan) {
			$userRepo = new UserRepository();
			$userRepo->createDefaultCarePlan($user, array());
			$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
				->first();
		}

		if($carePlan) {
			$carePlan->build($user->ID);
		}

		//problems for userheader
		$treating = array();
		if($carePlan) {
			$treating = (new ReportsService())->getProblemsToMonitorWithDetails($carePlan);
		}

		// locations @todo get location id for WpBlog
		$program = WpBlog::find($programId);
		$locations = array();
		if($program) {
			$locations = Location::where('parent_id', '=', $program->location_id)->lists('name', 'id');
		}

		// care plans
		$carePlans = CarePlan::where('program_id', '=', $programId)->lists('display_name', 'id');

		// States (for dropdown)
		$states = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");

		// timezones for dd
		$timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($timezones_raw as $timezone) {
			$timezones[$timezone] = $timezone;
		}

		//$showApprovalButton = false;
		$showApprovalButton = true; // always show
		if( Auth::user()->hasRole('provider') ) {
			if( $patient->carePlanStatus != 'provider_approved' ) {
				$showApprovalButton = true;
			}
		} else if( $patient->carePlanStatus == 'draft' ) {
			$showApprovalButton = true;
		}

		return view('wpUsers.patient.careplan.patient', compact(['patient', 'userMeta', 'userConfig','states', 'locations', 'timezones', 'messages', 'patientRoleId', 'programs', 'programId', 'showApprovalButton', 'carePlans', 'treating']));
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
		$patientId = false;
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$user = new User;
		if($patientId) {
			$user = User::with('meta')->find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
		}

		$userRepo = new UserRepository();

		if($patientId) {
			// validate
			$messages = [
				'required' => 'The :attribute field is required.',
				'study_phone_number.required' => 'The patient phone number field is required.',
			];
			$this->validate($request, $user->patient_rules, $messages);
			$userRepo->editUser($user, $params);
			if($params->get('direction')) {
				return redirect($params->get('direction'))->with('messages', ['Successfully updated patient demographics.']);
			}
			return redirect()->back()->with('messages', ['Successfully updated patient demographics.']);
		} else {
			// validate
			$messages = [
				'required' => 'The :attribute field is required.',
				'study_phone_number.required' => 'The patient phone number field is required.',
			];
			$this->validate($request, $user->patient_rules, $messages);
			$role = Role::whereName('participant')->first();
			$newUserId = str_random(15);
			$params->add(array(
				'user_login' => $newUserId,
				'user_email' => $newUserId . '@careplanmanager.com',
				'user_pass' => $newUserId,
				'user_status' => '1',
				'user_nicename' => 'Happy Gilmore',
				'program_id' => $params->get('program_id'),
				'roles' => [$role->id],
			));
			$newUser = $userRepo->createNewUser($user, $params);
			//$newUser = $userRepo->editUser($newUser, $params);
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

		$user = new User;
		if($patientId) {
			$user = User::find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
		}
		$patient = $user;

		// get program
		$programId = $user->program_id;

		// get user config
		$userMeta = UserMeta::where('user_id', '=', $patientId)->lists('meta_value', 'meta_key');
		$userConfig = unserialize($userMeta['wp_' . $programId . '_user_config']);
		$userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);

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
			$careTeamUsers[] = User::find($id);
		}

		// get providers
		$providers = array();
		$providers = User::whereIn('ID', Auth::user()->viewableUserIds())
			->with('meta')
			->whereHas('roles', function($q){
				$q->where('name', '=', 'provider');
			})->get();
		$phtml = '';

		$showApprovalButton = false;
		if( Auth::user()->hasRole('provider') ) {
			if( $patient->carePlanStatus != 'provider_approved' ) {
				$showApprovalButton = true;
			}
		} else if( $patient->carePlanStatus == 'draft' ) {
			$showApprovalButton = true;
		}

		// get careplan
		$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
			->first();

		if(!$carePlan) {
			$userRepo = new UserRepository();
			$userRepo->createDefaultCarePlan($user, array());
			$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
				->first();
		}

		if($carePlan) {
			$carePlan->build($user->ID);
		}

		//problems for userheader
		$treating = array();
		if($carePlan) {
			$treating = (new ReportsService())->getProblemsToMonitorWithDetails($carePlan);
		}

		return view('wpUsers.patient.careplan.careteam', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml', 'phtml', 'providers', 'careTeamUsers', 'showApprovalButton', 'treating']));
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
		$patient = User::with('meta')->find($patientId);
		if (!$patient) {
			return response("Patient user not found", 401);
		}

		// process form
		if($params->get('formSubmit') == "Save") {
			if($params->get('ctmCountArr')) {
				if(!empty($params->get('ctmCountArr'))) {
					// get provider specific info
					$careTeamUserIds = array();
					foreach( $_POST['ctmCountArr'] as $ctmCount) {
						if($params->get('ctm'.$ctmCount.'provider') && !empty($params->get('ctm'.$ctmCount.'provider'))) {
							$careTeamUserIds[] = $params->get('ctm'.$ctmCount.'provider');
						}
					}
					$user_config['care_team'] = $careTeamUserIds;
					$patient->careTeam = $user_config['care_team'];

					// get send alerts
					if($params->get('ctmsa') && !empty($params->get('ctmsa'))) {
						$user_config['send_alert_to'] = $params->get('ctmsa');
						$patient->sendAlertTo = $user_config['send_alert_to'];
					} else {
						$patient->sendAlertTo = '';
					}

					// get billing provider
					if($params->get('ctbp') && !empty($params->get('ctbp'))) {
						$user_config['billing_provider'] = $params->get('ctbp');
						$patient->billingProviderID = $user_config['billing_provider'];
					} else {
						$patient->billingProviderID = '';
					}

					// get lead contact
					if($params->get('ctlc') && !empty($params->get('ctlc'))) {
						$user_config['lead_contact'] = $params->get('ctlc');
						$patient->leadContactID = $user_config['lead_contact'];
					} else {
						$patient->leadContactID = '';
					}
				}
			}
		}

		if($params->get('direction')) {
			return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care team.']);
		}
		return redirect()->back()->with('messages', ['Successfully updated patient care team.']);
	}




	/**
	 * Display patient careplan
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientCareplan(Request $request, $patientId = false, $page) {
		$messages = \Session::get('messages');

		$user = false;
		if($patientId) {
			$user = User::find($patientId);
			if (!$user) {
				return response("User not found", 401);
			}
		}
		$patient = $user;
		$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
			->first();

		if(!$carePlan) {
			$userRepo = new UserRepository();
			$userRepo->createDefaultCarePlan($user, array());
			$carePlan = CarePlan::where('id', '=', $user->care_plan_id)
				->first();
		}

		if($carePlan) {
			$carePlan->build($user->ID);
		}

		// determine which sections to show
		if($page == 1) {
			$careSectionNames = array(
				'diagnosis-problems-to-monitor',
				'lifestyle-to-monitor',
				'medications-to-monitor',
			);
		} else if($page == 2) {
			$careSectionNames = array(
				'biometrics-to-monitor',
				'transitional-care-management',
			);
		} else if($page == 3) {
			$careSectionNames = array(
				'symptoms-to-monitor',
				'additional-information',
				//'misc',
			);
		}
		$editMode = false;

		$showApprovalButton = false;
		if( Auth::user()->hasRole('provider') ) {
			if( $patient->carePlanStatus != 'provider_approved' ) {
				$showApprovalButton = true;
			}
		} else if( $patient->carePlanStatus == 'draft' ) {
			$showApprovalButton = true;
		}

		//problems for userheader
		$treating = array();
		if($carePlan) {
			$treating = (new ReportsService())->getProblemsToMonitorWithDetails($carePlan);
		}

		return view('wpUsers.patient.careplan.careplan', compact(['page', 'careSectionNames', 'patient', 'editMode', 'carePlan', 'messages', 'showApprovalButton', 'treating']));
	}

	/**
	 * Store patient careplan
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
		$user = User::with('meta')->find($patientId);
		if (!$user) {
			return response("User not found", 401);
		}

		// get page
		$page = $params->get('page');
		if($page == 3) {
			// check for approval here
			// should we update careplan_status?
			if($user->carePlanStatus != 'provider_approved') {
				if (Auth::user()->hasRole('provider')) {
					$user->carePlanStatus = 'provider_approved'; // careplan_status
					$user->carePlanProviderApprover = Auth::user()->ID; // careplan_provider_approver
					$user->carePlanProviderApproverDate = date('Y-m-d H:i:s'); // careplan_provider_date
				} else {
					$user->carePlanStatus = 'qa_approved'; // careplan_status
					$user->carePlanQaApprover = Auth::user()->ID; // careplan_qa_approver
					$user->carePlanQaDate = date('Y-m-d H:i:s'); // careplan_qa_date
				}
				$user->save();
			}
		}

		// get carePlan
		$careplan = CarePlan::find($params->get('careplan_id'));
		if (!$careplan) {
			if($params->get('direction')) {
				return redirect($params->get('direction'))->with('messages', ['No care plan found to update.']);
			}
			return redirect()->back()->with('errors', ['No care plan found to update.']);
		}

		// loop through care plan items in viewed sections
		if($params->get('careSections')) {
			$sectionCareItems = $careplan->careItems()->whereIn('section_id', $params->get('careSections'))->get();
			foreach ($sectionCareItems as $careItem) {
				$carePlanItem = CarePlanItem::where('item_id', '=', $careItem->id)
					->where('plan_id', '=', $careplan->id)
					->first();
				if (!$carePlanItem) {
					continue 1;
				}
				$value = $params->get('item|' . $carePlanItem->id);
				// if checkbox and unchecked on the ui it doesnt post, so set these to Inactive
				if (!$value && ($carePlanItem->ui_fld_type == 'SELECT' || $carePlanItem->ui_fld_type == 'CHECK')) {
					$value = 'Inactive';
				}
				if ($value) {
					// update user item
					$carePlanItem->meta_value = $careplan->setCareItemUserValue($user, $carePlanItem->careItem->name, $value);
				}
			}
		}

		if($params->get('direction')) {
			return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care plan.']);
		}
		return redirect()->back()->with('messages', ['successfully updated patient care plan']);
	}

	/**
	 * Display patient careplan
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	/*
	public function showPatientCareplanWP(Request $request, $patientId = false)
	{
		$messages = \Session::get('messages');

		$wpUser = false;
		if($patientId) {
			$wpUser = User::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}
		$patient = $wpUser;

		// program
		$program = WpBlog::find($wpUser->program_id);

		$params = $request->all();

		// user config
		$userConfig = (new UserConfigTemplate())->getArray();
		if(isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
			$userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
			$userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
		}

		//$sectionHtml = $carePlanUI->renderCareplanSection($wpUser, 'Biometrics to Monitor');
		$sectionHtml = (new CareplanUIService)->renderCareplanSections(array(), $wpUser->program_id, $wpUser);

		return view('wpUsers.patient.careplan.careplan', compact(['program','patient', 'userConfig', 'messages', 'sectionHtml']));
	}
	*/

	/**
	 * Save patient careplan
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	/*
	public function storePatientCareplanWP(Request $request)
	{
		// input
		$params = new ParameterBag($request->input());
		if($params->get('user_id')) {
			$patientId = $params->get('user_id');
		}

		// instantiate user
		$wpUser = User::with('meta')->find($patientId);
		if (!$wpUser) {
			return response("User not found", 401);
		}

		if($params->get('direction')) {
			return redirect($params->get('direction'))->with('messages', ['Successfully updated patient care plan.']);
		}
		return redirect()->back()->with('messages', ['successfully updated patient care plan']);

		//return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
	}
	*/
}
