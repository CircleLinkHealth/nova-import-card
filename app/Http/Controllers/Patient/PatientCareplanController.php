<?php namespace App\Http\Controllers\Patient;

use App\Activity;
use App\Observation;
use App\WpBlog;
use App\Location;
use App\WpUser;
use App\WpUserMeta;
use App\Role;
use App\Services\ActivityService;
use App\Services\CareplanService;
use App\Services\ObservationService;
use App\Services\MsgUser;
use App\Services\MsgUI;
use App\Services\MsgChooser;
use App\Services\MsgScheduler;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;
use PasswordHash;
use Auth;
use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PatientCareplanController extends Controller {

	/**
	 * Display Careplan
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showPatientCareplan(Request $request, $programId, $id = false)
	{
		$wpUser = array();
		if($id) {
			$wpUser = WpUser::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		// program
		$program = WpBlog::find($programId);

		return view('wpUsers.patient.careplan.careplan', ['program' => $program, 'patient' => $wpUser]);
	}


	/**
	 * Save Careplan
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function savePatientCareplan(Request $request, $programId, $id = false)
	{
		$params = $request->all();

		// validate
		$rules = array("user_id" => "required",
		  "daily_reminder_optin" => "required",
		  "daily_reminder_time" => "required",
		  "daily_reminder_areas" => "required",
		  "hospital_reminder_optin" => "required",
		  "hospital_reminder_time" => "required",
		  "hospital_reminder_areas" => "required",
		  "qualification" => "required",
		  "specialty" => "required",
		  "npi_number" => "required",
		  "firstName" => "required",
		  "lastName" => "required",
		  "gender" => "required",
		  "mrn_number" => "required",
		  "DOBMonth" => "required",
		  "DOBDay" => "required",
		  "DOBYear" => "required",
		  "telephone" => "required",
		  "email" => "required",
		  "address" => "required",
		  "city" => "required",
		  "state" => "required",
		  "zip" => "required",
		  "preferred_contact_time" => "required",
		  "timezone" => "required",
		  "CDateMonth" => "required",
		  "CDateDay" => "required",
		  "CDateYear" => "required",);

		// validate
		$this->validate($request, $rules);

		// return back
		return redirect()->back()->withInput()->with('messages', ['successfully updated role'])->send();
		$wpUser = array();
		if($id) {
			$wpUser = WpUser::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		// program
		$program = WpBlog::find($programId);

		return view('wpUsers.patient.careplan.careplan', ['program' => $program, 'patient' => $wpUser]);
	}

	/**
	 * Display Careplan Print
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showPatientCareplanPrint(Request $request, $programId, $id = false)
	{
		$wpUser = array();
		if($id) {
			$wpUser = WpUser::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		// program
		$program = WpBlog::find($programId);

		return view('wpUsers.patient.careplan.print', ['program' => $program, 'patient' => $wpUser]);
	}
}
