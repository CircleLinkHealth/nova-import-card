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
	 * Display Careplan
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientCareplan(Request $request, $patientId = false)
	{
		$wpUser = array();
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}
		$patient = $wpUser;

		// program
		$program = WpBlog::find($wpUser->program_id);

		// States (for dropdown)
		$states = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");

		// locations
		$locations = Location::where('program_id', '=', $wpUser->program_id)->lists('name', 'id');

		// timezones
		$timezones_raw = $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($timezones_raw as $timezone) {
			$timezones[$timezone] = $timezone;
		}

		$carePlanUI = new CareplanUIService;
		//$sectionHtml = $carePlanUI->renderCareplanSection($wpUser, 'Biometrics to Monitor');
		$sectionHtml = $carePlanUI->renderCareplanSections(array(), $wpUser->program_id, $wpUser);
		//dd($sectionHtml);

		return view('wpUsers.patient.careplan.careplan', compact(['program','patient','states', 'locations', 'timezones', 'sectionHtml']));
	}


	/**
	 * Save Careplan
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function savePatientCareplan(Request $request, $patientId = false)
	{
		// instantiate user
		$wpUser = new WpUser;
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		$params = new ParameterBag($request->input());

		$userRepo = new WpUserRepository();

		$wpUser = new WpUser;

		// validate
		$this->validate($request, $wpUser->rules);

		$wpUser = $userRepo->createNewUser($wpUser, $params);

		// return back
		return redirect()->back()->withInput()->with('messages', ['successfully created/updated patient'])->send();

		// program
		$program = WpBlog::find($wpUser->program_id);

		return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
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
