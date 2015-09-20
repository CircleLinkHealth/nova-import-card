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
		// instantiate user
		$wpUser = new WpUser;
		if($id) {
			$wpUser = WpUser::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		// validate
		$this->validate($request, $wpUser->patient_rules);

		$params = $request->all();

		// return back
		return redirect()->back()->withInput()->with('messages', ['successfully created/updated patient'])->send();

		// program
		$program = WpBlog::find($programId);

		return view('wpUsers.patient.careplan', ['program' => $program, 'patient' => $wpUser]);
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
