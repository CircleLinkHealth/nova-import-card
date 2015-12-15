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

class PatientController extends Controller {

	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function showDashboard(Request $request)
	{
		// get number of approvals
		$patients = WpUser::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get();
		$p=0;
		if($patients->count() > 0) {
			foreach ($patients as $user) {
				$userMeta = $user->userMeta();
				if(!isset($userMeta['careplan_status'])) {
					continue 1;
				}
				$careplan_status = $userMeta['careplan_status'];
				// patient approval counts
				if(Auth::user()->hasRole(['administrator', 'care-center'])) {
					// care-center and administrator counts number of drafts
					if ($careplan_status == 'draft') {
						$p++;
					}
				} else if(Auth::user()->hasRole(['provider'])) {
					// provider counts number of drafts
					if ($careplan_status == 'qa_approved') {
						$p++;
					}

				}
			}
		}
		$pendingApprovals = $p;

		return view('wpUsers.patient.dashboard', compact(['pendingApprovals']));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientSummary(Request $request, $patientId)
	{
		$messages = \Session::get('messages');

		$wpUser = WpUser::find($patientId);
		if(!$wpUser) {
			return response("User not found", 401);
		}

		// security
		if(!Auth::user()->can('observations-view')) {
			abort(403);
		}

		// program
		$program = WpBlog::find($wpUser->program_id);

		$params = $request->all();
		$detailSection = '';
		if(isset($params['detail'])) {
			$detailSection = $params['detail'];
		}

		$sections = array(
			array('section' => 'obs_biometrics', 'id' => 'obs_biometrics_dtable', 'title' => 'Biometrics', 'col_name_question' => 'Reading Type', 'col_name_severity' => 'Reading'),
			array('section' => 'obs_medications', 'id' => 'obs_medications_dtable', 'title' => 'Medications', 'col_name_question' => 'Medication', 'col_name_severity' => 'Adherence'),
			array('section' => 'obs_symptoms', 'id' => 'obs_symptoms_dtable', 'title' => 'Symptoms', 'col_name_question' => 'Symptom', 'col_name_severity' => 'Severity'),
			array('section' => 'obs_lifestyle', 'id' => 'obs_lifestyle_dtable', 'title' => 'Lifestyle', 'col_name_question' => 'Question', 'col_name_severity' => 'Response'),
		);

		$observations = Observation::where('user_id' ,'=', $wpUser->ID);
		$observations->where('obs_unit' ,'!=', "invalid");
		$observations->where('obs_unit' ,'!=', "scheduled");
		$observations->orderBy('obs_date' ,'desc');
		$observations = $observations->get();

		// build array of pcp
		$obs_by_pcp = array(
			'obs_biometrics' => array(),
			'obs_medications' => array(),
			'obs_symptoms' => array(),
			'obs_lifestyle' => array(),
		);
		foreach($observations as $observation) {
			if($observation['obs_value'] == '') {
				//$obs_date = date_create($observation['obs_date']);
				//if( (($obs_date->format('Y-m-d')) < date("Y-m-d")) && $observation['obs_key'] == 'Call' ) {
				if( $observation['obs_key'] != 'Call' ) { // skip NR's, which are any obs that has no value (other than call)
					continue 1;
				}
			}
			$observation['parent_item_text'] = '---';
			switch ($observation["obs_key"]) {
				case 'HSP':
				case 'HSP_ER':
				case 'HSP_HOSP':
					break;
				case 'Blood_Pressure':
				case 'Blood_Sugar':
				case 'Cigarettes':
				case 'Weight':
					$obs_by_pcp['obs_biometrics'][] = $observation;
					break;
				case 'Adherence':
					$obs_by_pcp['obs_medications'][] = $observation;
					break;
				//case 'Symptom':
				case 'Severity':
					//$obs_info = $this->cpm_1_7_datamonitor_library->process_alert_obs_severity($user_data_ucp, $observation, $this->get('blog_id'));
					if(!empty($obs_info['extra_vars']['symptom'])) {
						$observation['items_text'] = $obs_info['extra_vars']['symptom'];
						$observation['description'] = $obs_info['extra_vars']['symptom'];
						$observation['obs_key'] = $obs_info['extra_vars']['symptom'];
					}
					$obs_by_pcp['obs_symptoms'][] = $observation;
					break;
				case 'Other':
				case 'Call':
					// only y/n responses, skip anything that is a number as its assumed it is response to a list
					if( ($observation['obs_key'] == 'Call') || (!is_numeric($observation['obs_value'])) ) {
						$obs_by_pcp['obs_lifestyle'][] = $observation;
					}
					break;
				default:
					break;
			}
		}

		// At this point, everything that didnt match went to lifestyle
		// get array of lifestyle questions, and only include these in obs_lifestyle (also include Call observations!)

		//$lifestyle_questions = $this->rules_model->getQuestionIdsByPCP(2, 7);
		$lifestyle_questions = array();
		$lifestyle_msg_ids = array();
		$filtered_lifestyle_obs = array();
		foreach($lifestyle_questions as $lifestyle_question) {
			$lifestyle_msg_ids[] = $lifestyle_question['msg_id'];
		}

		foreach($obs_by_pcp['obs_lifestyle'] as $lifestyle_obs) {
			if((($lifestyle_obs['obs_key'] == 'Call')) || (in_array($lifestyle_obs['obs_message_id'], $lifestyle_msg_ids) && $lifestyle_obs['obs_value'] != '')) {
				$filtered_lifestyle_obs[] = $lifestyle_obs;
			}
		}
		$obs_by_pcp['obs_lifestyle'] = $filtered_lifestyle_obs;

		$observation_json = array();
		foreach($obs_by_pcp as $section => $observations) {
			$o=0;
			$observation_json[$section] = "data:[";
			foreach ($observations as $observation) {
				// limit to 3 if not detail
				if(empty($detailSection)) {
					if ($o >= 3) {
						continue 1;
					}
				}
				// lastly format json
				$observation_json[$section] .= "{ obs_key:'" . $observation->obs_key . "', " .
					"description:'" . $observation->items_text . '|' . $observation->obs_key . '|'.$observation->legacy_obs_id . "', " .
					"obs_value:'" . $observation->obs_value . "', " .
					"dm_alert_level:'default', " .
					"obs_unit:'" . $observation->obs_unit . "', " .
					"obs_message_id:'" . $observation->obs_message_id . "', " .
					"comment_date:'".$observation->obs_date."', " . "},";
				$o++;
			}
			$observation_json[$section] .= "],";
		}

		//return response()->json($cpFeed);
		return view('wpUsers.patient.summary', ['program' => $program, 'patient' => $wpUser, 'wpUser' => $wpUser, 'sections' => $sections, 'detailSection' => $detailSection, 'observation_data' => $observation_json, 'messages' => $messages]);
	}


	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function showPatientListing(Request $request)
	{
		// get number of approvals
		$patients = WpUser::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get();
		$p=0;
		if($patients->count() > 0) {
			foreach ($patients as $user) {
				$userMeta = $user->userMeta();
				if(!isset($userMeta['careplan_status'])) {
					continue 1;
				}
				$careplan_status = $userMeta['careplan_status'];
				// patient approval counts
				if(Auth::user()->hasRole(['administrator', 'care-center'])) {
					// care-center and administrator counts number of drafts
					if ($careplan_status == 'draft') {
						$p++;
					}
				} else if(Auth::user()->hasRole(['provider'])) {
					// provider counts number of drafts
					if ($careplan_status == 'qa_approved') {
						$p++;
					}

				}
			}
		}
		$pendingApprovals = $p;

		return view('wpUsers.patient.dashboard', compact(['pendingApprovals']));
	}

	/**
	 * Select Program
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showSelectProgram(Request $request, $patientId = false)
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

		return view('wpUsers.patient.alerts', ['program' => $program, 'patient' => $wpUser]);
	}


	/**
	 * Display Alerts
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientAlerts(Request $request, $patientId = false)
	{
		$wpUser = array();
		if($patientId) {
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		return view('wpUsers.patient.alerts', ['patient' => $wpUser]);
	}



	/**
	 * Display Notes
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientNotes(Request $request, $patientId = false)
	{
		$wpUser = array();
		if($patientId) {
			// patient view
			$wpUser = WpUser::find($patientId);
			if (!$wpUser) {
				return response("User not found", 401);
			}
			// program
			$program = WpBlog::find($wpUser->program_id);
		} else {
			// program view
		}

		return view('wpUsers.patient.notes', ['program' => $program, 'patient' => $wpUser]);
	}





	/**
	 * Display Observation Create
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function showPatientObservationCreate(Request $request, $patientId = false)
	{
		$patient = array();
		if($patientId) {
			$patient = WpUser::find($patientId);
			if (!$patient) {
				return response("User not found", 401);
			}
		}

		// security
		if(!Auth::user()->can('observations-create')) {
			abort(403);
		}

		return view('wpUsers.patient.observation.create', ['patient' => $patient]);
	}
}
