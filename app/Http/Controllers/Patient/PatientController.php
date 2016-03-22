<?php namespace App\Http\Controllers\Patient;

use App\Activity;
use App\CPRulesQuestions;
use App\Observation;
use App\Services\ReportsService;
use App\CarePlan;
use App\CareItem;
use App\CarePlanItem;
use App\WpBlog;
use App\Location;
use App\User;
use App\UserMeta;
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
use Illuminate\Support\Facades\Input;
use PasswordHash;
use Auth;
use DB;
use URL;
use Carbon\Carbon;

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
		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
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

		if (! $impersonatedUserEmail = $request->input('impersonatedUserEmail'))
		{
			$impersonatedUserEmail = '';
		}

		return view('wpUsers.patient.dashboard', compact(['pendingApprovals', 'impersonatedUserEmail']));
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

		$wpUser = User::find($patientId);
		if(!$wpUser) {
			return response("User not found", 401);
		}

		// security
		if(!Auth::user()->can('observations-view')) {
			abort(403);
		}

		// program
		$program = WpBlog::find($wpUser->program_id);

		$carePlan = CarePlan::where('id', '=', $wpUser->care_plan_id)
			->first();

		if($carePlan) {
			$carePlan->build($wpUser->ID);
		}

		//problems for userheader
		$treating = array();
		if($carePlan) {
			$treating = (new ReportsService())->getProblemsToMonitorWithDetails($carePlan);
		}

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
				case 'Cigarettes':
					$observation['description'] = 'Smoking (# per day)';
					$obs_by_pcp['obs_biometrics'][] = $observation;
					break;
				case 'Blood_Pressure':
				case 'Blood_Sugar':
				case 'Weight':
					$observation['description'] = $observation["obs_key"];
					$obs_by_pcp['obs_biometrics'][] = $observation;
					break;
				case 'Adherence':
					$question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
					// find carePlanItem with qid
					if($question) {
						$item = CareItem::where('qid', '=', $question->qid)->first();
						if($item) {
							$observation['description'] = $item->display_name;
						}
					}
					$obs_by_pcp['obs_medications'][] = $observation;
					break;
				case 'Symptom':
				case 'Severity':
					// get description
					$question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
					if($question) {
						$observation['items_text'] = $question->description;
						$observation['description'] = $question->description;
						$observation['obs_key'] = $question->description;
					}
					$obs_by_pcp['obs_symptoms'][] = $observation;
					break;
				case 'Other':
				case 'Call':
					// only y/n responses, skip anything that is a number as its assumed it is response to a list
					$question = CPRulesQuestions::where('msg_id', '=', $observation->obs_message_id)->first();
					// find carePlanItem with qid
					if($question) {
						$item = CareItem::where('qid', '=', $question->qid)->first();
						if($item) {
							$observation['description'] = $item->display_name;
						}
					}
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
		/*
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
		*/

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
				// set default
				$alertLevel = 'default';
				if(!empty($observation->alert_level )) {
					$alertLevel = $observation->alert_level;
				}
				// lastly format json
				$observation_json[$section] .= "{ obs_key:'" . $observation->obs_key . "', " .
					"description:'" . str_replace('_', " ", $observation->description) . "', " .
					"obs_value:'" . $observation->obs_value . "', " .
					"dm_alert_level:'" . $alertLevel . "', " .
					"obs_unit:'" . $observation->obs_unit . "', " .
					"obs_message_id:'" . $observation->obs_message_id . "', " .
					"comment_date:'".Carbon::parse($observation->obs_date)->format('m-d-y h:i:s A')."', " . "},";
				$o++;
			}
			$observation_json[$section] .= "],";
		}

		//dd($observation_json);
		//return response()->json($cpFeed);
		return view('wpUsers.patient.summary', ['program' => $program, 'patient' => $wpUser, 'wpUser' => $wpUser, 'sections' => $sections, 'detailSection' => $detailSection, 'observation_data' => $observation_json, 'messages' => $messages, 'treating' => $treating]);
	}


	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function showPatientListing(Request $request)
	{
		$patientData = array();
		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
				->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get();
		if($patients->count() > 0) {
			foreach ($patients as $patient) {
				// skip if patient has no name
				if(empty($patient->firstName)) {
					continue 1;
				}
				// careplan status stuff from 2.x
				$careplanStatus = $patient->carePlanStatus;
				$careplanStatusLink = '';
				$approverName = 'NA';
				$tooltip = 'NA';
				if ($patient->carePlanStatus  == 'provider_approved') {
					$approverId = $patient->carePlanProviderApprover;
					$approver = User::find($approverId);
					if($approver) {
						$approverName = $approver->fullName;
						$careplanStatus = 'Approved';
						$careplanStatusLink = '<span data-toggle="" title="' . $approver->fullName . ' ' . $patient->carePlanProviderDate . '">Approved</span>';
						$tooltip = $approverName . ' ' . $patient->carePlanProviderDate;
					}
				} else if ($patient->carePlanStatus == 'qa_approved') {
					$careplanStatus = 'Approve Now';
					$tooltip = $careplanStatus;
					$careplanStatusLink = 'Approve Now';
					if (Auth::user()->hasRole('provider')) {
						$careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>Approve Now</strong></a>';
					}
				} else if ($patient->carePlanStatus == 'draft') {
					$careplanStatus = 'CLH Approve';
					$tooltip = $careplanStatus;
					$careplanStatusLink = 'CLH Approve';
					if (Auth::user()->hasRole('care-center') || Auth::user()->hasRole('administrator')) {
						$careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>CLH Approve</strong></a>';
					}
				}

				// get billing provider name
				$bpName = '';
				if(!empty($patient->billingProviderID)) {
					$bpUser = User::find($patient->billingProviderID);
					if($bpUser) {
						$bpName = $bpUser->fullName;
					}
				}

				// get date of last observation
				$lastObservationDate = 'No Readings';
				$lastObservation = $patient->observations()->where('obs_key', '!=', 'Outbound')->orderBy('obs_date', 'DESC')->first();
				if(!empty($lastObservation)) {
					$lastObservationDate = date("m/d/Y", strtotime($lastObservation->obs_date));
				}

				$patientData[] = array('key' => $patient->ID, // $part->ID,
					'patient_name' => $patient->fullName, //$meta[$part->ID]["first_name"][0] . " " .$meta[$part->ID]["last_name"][0],
					'first_name' => $patient->firstName, //$meta[$part->ID]["first_name"][0],
					'last_name' => $patient->lastName, //$meta[$part->ID]["last_name"][0],
					'ccm_status' => ucfirst($patient->ccmStatus), //ucfirst($meta[$part->ID]["ccm_status"][0]),
					'careplan_status' => $careplanStatus, //$careplanStatus,
					'tooltip' => $tooltip, //$tooltip,
					'careplan_status_link' => $careplanStatusLink, //$careplanStatusLink,
					'careplan_provider_approver' => $approverName, //$approverName,
					'dob' => Carbon::parse($patient->birthDate)->format('m/d/Y'), //date("m/d/Y", strtotime($user_config[$part->ID]["birth_date"])),
					'phone' => $patient->phone, //$user_config[$part->ID]["study_phone_number"],
					'age' => $patient->age,
					'reg_date' => Carbon::parse($patient->registrationDate)->format('m/d/Y'), //date("m/d/Y", strtotime($user_config[$part->ID]["registration_date"])) ,
					'last_read' => $lastObservationDate, //date("m/d/Y", strtotime($last_read)),
					'ccm_time' => $patient->monthlyTime, //$ccm_time[0],
					'ccm_seconds' => $patient->monthlyTime, //$meta[$part->ID]['cur_month_activity_time'][0]
					'provider'=> $bpName, // $bpUserInfo['prefix'] . ' ' . $bpUserInfo['first_name'] . ' ' . $bpUserInfo['last_name'] . ' ' . $bpUserInfo['qualification']
				);
			}
		}
		$patientJson = json_encode($patientData);



		return view('wpUsers.patient.listing', compact(['pendingApprovals', 'patientJson']));
	}


	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function showPatientCarePlanPrintList(Request $request)
	{
		$patientData = array();
		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get();
		if($patients->count() > 0) {
			foreach ($patients as $patient) {
				// skip if patient has no name
				if(empty($patient->firstName)) {
					continue 1;
				}
				// careplan status stuff from 2.x
				$careplanStatus = $patient->carePlanStatus;
				$careplanStatusLink = '';
				$approverName = 'NA';
				if ($patient->carePlanStatus  == 'provider_approved') {
					$approverId = $patient->carePlanProviderApprover;
					$approver = User::find($approverId);
					if($approver) {
						$approverName = $approver->fullName;
						$careplanStatus = 'Approved';
						$careplanStatusLink = '<span data-toggle="" title="' . $approver->fullName . ' ' . $patient->carePlanProviderDate . '">Approved</span>';
						$tooltip = $approverName . ' ' . $patient->carePlanProviderDate;
					}
				} else if ($patient->carePlanStatus == 'qa_approved') {
					$careplanStatus = 'Approve Now';
					$tooltip = $careplanStatus;
					$careplanStatusLink = 'Approve Now';
					if (Auth::user()->hasRole('provider')) {
						$careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>Approve Now</strong></a>';
					}
				} else if ($patient->carePlanStatus == 'draft') {
					$careplanStatus = 'CLH Approve';
					$tooltip = $careplanStatus;
					$careplanStatusLink = 'CLH Approve';
					if (Auth::user()->hasRole('care-center') || Auth::user()->hasRole('administrator')) {
						$careplanStatusLink = '<a style="text-decoration:underline;" href="' . URL::route('patient.demographics.show', array('patient' => $patient->ID)) . '"><strong>CLH Approve</strong></a>';
					}
				}

				// get billing provider name
				$bpName = '';
				if(!empty($patient->billingProviderID)) {
					$bpUser = User::find($patient->billingProviderID);
					if($bpUser) {
						$bpName = $bpUser->fullName;
					}
				}

				// get date of last observation
				$lastObservationDate = 'No Readings';
				$lastObservation = $patient->observations()->where('obs_key', '!=', 'Outbound')->orderBy('obs_date', 'DESC')->first();
				if(!empty($lastObservation)) {
					$lastObservationDate = date("m/d/Y", strtotime($lastObservation->obs_date));
				}

				$patientData[] = array('key' => $patient->ID, // $part->ID,
					'patient_name' => $patient->fullName, //$meta[$part->ID]["first_name"][0] . " " .$meta[$part->ID]["last_name"][0],
					'first_name' => $patient->firstName, //$meta[$part->ID]["first_name"][0],
					'last_name' => $patient->lastName, //$meta[$part->ID]["last_name"][0],
					'ccm_status' => ucfirst($patient->ccmStatus), //ucfirst($meta[$part->ID]["ccm_status"][0]),
					'careplan_status' => $careplanStatus, //$careplanStatus,
					'tooltip' => $tooltip, //$tooltip,
					'careplan_status_link' => $careplanStatusLink, //$careplanStatusLink,
					'careplan_provider_approver' => $approverName, //$approverName,
					'dob' => Carbon::parse($patient->birthDate)->format('m/d/Y'), //date("m/d/Y", strtotime($user_config[$part->ID]["birth_date"])),
					'phone' => $patient->phone, //$user_config[$part->ID]["study_phone_number"],
					'age' => $patient->age,
					'reg_date' => Carbon::parse($patient->registrationDate)->format('m/d/Y'), //date("m/d/Y", strtotime($user_config[$part->ID]["registration_date"])) ,
					'last_read' => $lastObservationDate, //date("m/d/Y", strtotime($last_read)),
					'ccm_time' => $patient->monthlyTime, //$ccm_time[0],
					'ccm_seconds' => $patient->monthlyTime, //$meta[$part->ID]['cur_month_activity_time'][0]
					'provider'=> $bpName, // $bpUserInfo['prefix'] . ' ' . $bpUserInfo['first_name'] . ' ' . $bpUserInfo['last_name'] . ' ' . $bpUserInfo['qualification']
				);
			}
		}
		$patientJson = json_encode($patientData);



		return view('wpUsers.patient.carePlanPrintList', compact(['pendingApprovals', 'patientJson']));
	}

	/**
	 * Display the specified resource.
	 *
	 * @return Response
	 */
	public function showPatientSelect(Request $request)
	{
		// get number of approvals
		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get()->lists('fullNameWithId', 'ID');

		return view('wpUsers.patient.select', compact(['patients']));
	}

	public function queryPatient(Request $request){
		$input = $request->all();
		$query = $input['users'];
		$data = User::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) use ($query) {
				$q->where('name', '=', 'participant');
			})
			->where('display_name', 'LIKE', "%$query%")
			->get()->lists('ID');
		$patients = array();
		$i = 0;
		foreach($data as $d){
			$patients[$i]['name'] = (User::find($d)->display_name);
			$patients[$i]['name'] = (User::find($d)->display_name);
			$dob = new Carbon((User::find($d)->getBirthDateAttribute()));
			$patients[$i]['dob'] = $dob->format('m-d-Y');
			$patients[$i]['mrn'] = (User::find($d)->getMRNAttribute());
			$patients[$i]['link'] = URL::route('patient.summary', array('patient' => $d));
			$programObj = WpBlog::find((User::find($d)->blogId())) ? WpBlog::find((User::find($d)->blogId())) : "";
			if($programObj->display_name){
				$patients[$i]['program'] = $programObj->display_name;
			} else { $patients[$i]['program'] = '';}
			$patients[$i]['hint'] = $patients[$i]['name'] . " " . $patients[$i]['program'] . " " . $patients[$i]['dob'];
			$i++;
		}$patients = (object) $patients;
		return response()->json($patients);
	}

		public function patientAjaxSearch(Request $request){

		$data = User::whereIn('ID', Auth::user()->viewablePatientIds())
			->with('meta')->whereHas('roles', function($q) {
				$q->where('name', '=', 'participant');
			})->get()->lists('ID');
		$patients = '[';
		$i = 0;
		foreach($data as $d){
			$name = (User::find($d)->getFullNameAttribute());
			$dob = new Carbon((User::find($d)->getBirthDateAttribute()));
			$dob = $dob->format('m-d-Y');
			$mrn = (User::find($d)->getMRNAttribute());
			$programObj = WpBlog::find((User::find($d)->blogId())) ? WpBlog::find((User::find($d)->blogId())) : "";
			if($programObj->display_name){
				$program = $programObj->display_name;
			} else { $program = '';}
			$search = $name .' | '. $dob .' | ' . $mrn;
			if($i == count($data) - 1){
				$patients .=  '{ DOB: "'. $dob .'", program: "'. $program .'", search: "'. $search .'", id : "' . $d . '", link: "' . URL::route('patient.summary', array('patient' => $d)). '", name: "'. $name .'"}] ';
			} else {
				$patients .= '{ DOB: "'. $dob .'", program: "'. $program .'", search: "'. $search .'", id : "' .  $d . '", link: "'.URL::route('patient.summary', array('patient' => $d)).'", name: "'.$name.'"}, ';
			}
			$i++;
		}

		return view('wpUsers.patient.select',  ['data' => $patients]);
	}
	/**
	 * Process the specified resource.
	 *
	 * @return Response
	 */
	public function processPatientSelect(Request $request)
	{
		$params = $request->all();
		if (!empty($params)) {
			if (isset($params['findUser'])) {
				$user = User::find($params['findUser']);
				if($user) {
					return redirect()->route('patient.summary', [$params['findUser']]);
				}
			}
		}
		return redirect()->route('patient.dashboard', [$params['findUser']]);
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
			$wpUser = User::find($patientId);
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
			$wpUser = User::find($patientId);
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
			$wpUser = User::find($patientId);
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
			$patient = User::find($patientId);
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
