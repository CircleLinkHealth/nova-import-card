<?php namespace App\Http\Controllers;

use App\Activity;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Services\CareplanService;
use App\Services\ReportsService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ReportsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request, $patientId = false)
	{
		$treating = array();

		$dat = (new ReportsService())->progress($patientId);
		$user = User::find($patientId);

		//PCP has the sections for each provider, get all sections for the user's blog
		$pcp = CPRulesPCP::where('prov_id', '=', $user->blogId())->where('status', '=', 'Active')->where('section_text', 'Diagnosis / Problems to Monitor')->first();

		//Get all the items for each section
		$items = CPRulesItem::where('pcp_id', $pcp->pcp_id)->where('items_parent', 0)->lists('items_id');
		for ($i = 0; $i < count($items); $i++) {
			//get id's of all lifestyle items that are active for the given user
			$item_for_user[$i] = CPRulesUCP::where('items_id', $items[$i])->where('meta_value', 'Active')->where('user_id', $user->ID)->first();
			$items_detail[$i] = CPRulesItem::where('items_parent', $items[$i])->first();
			$items_detail_ucp[$i] = CPRulesUCP::where('items_id', $items_detail[$i]->items_id)->where('user_id', $user->ID)->first();
			if ($item_for_user[$i] != null) {
				//Find the items_text for the one's that are active
				$user_items = CPRulesItem::find($item_for_user[$i]->items_id);
				$treating[] = $user_items->items_text;
			}
		}

		$biometrics = ['Weight','Blood_Sugar','Blood_Pressure'];
		$biometrics_data = array();
		$biometrics_array = array();

		foreach($biometrics as $biometric){
			$biometrics_data[$biometric] =
					DB::table('observations')
						->select(DB::raw('user_id, replace(obs_key,\'_\',\' \') \'Observation\',
					week(obs_date) week, year(obs_date) year, floor(datediff(now(), obs_date)/7) realweek,
					date_format(max(obs_date), \'%c/%e\') as day, date_format(min(obs_date), \'%c/%e\') as day_low,
					min(obs_date) as min_obs_id, max(obs_date) as obs_id,
					round(avg(obs_value)) \'Avg\''))
						->where('obs_key', '=' ,$biometric)
						->where('user_id', $user->ID)
						->where(DB::raw('datediff(now(), obs_date)/7'),'<=', 11)
						->where('obs_unit', '!=', 'invalid')
						->where('obs_unit', '!=', 'scheduled')
						->groupBy('user_id')
						->groupBy('obs_key')
						->groupBy('realweek')
						->orderBy('obs_date')
						->get();

		}			//debug($biometrics_data);

		foreach($biometrics_data as $key => $value){
			debug($key);
			$bio_name = $key;
			if($value != null) {
				$first = reset($value);
				$last = end($value);
				$biometrics_array[$bio_name]['change'] = intval($last->Avg) - intval($first->Avg);
				$biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);
			}

			if($first < $last) {
				$biometrics_array[$bio_name]['change_arrow'] = 'up';
			} else if($first > $last) {
				$biometrics_array[$bio_name]['change_arrow'] = 'down';
			}

			$count = 1;
			$biometrics_array[$bio_name]['data'] = '';
			$biometrics_array[$bio_name]['max'] = -1;
			//$first = reset($array);
			if($value){
				foreach($value as $key => $value){
					$biometrics_array[$bio_name]['unit'] = (new ReportsService())->biometricsUnitMapping(str_replace('_', ' ',$bio_name));
					$biometrics_array[$bio_name]['reading'] = intval($value->Avg);
					if (intval($value->Avg) > $biometrics_array[$bio_name]['max']){
						$biometrics_array[$bio_name]['max'] = intval($value->Avg);
					}
					$biometrics_array[$bio_name]['data'] .= '{ id:'.$count.', Week:\''.$value->day.'\', Reading:'.intval($value->Avg).'} ,';
					$count++;
				}
			} else {
				unset($biometrics_array[$bio_name]);
			}
		}

		//Medication Tracking:
		$medications = (new ReportsService())->medicationStatus($user);

		$provider_data = array();


		$data = [
			'treating' => $treating,
			'patientId'	=> $patientId,
			'patient'=> $user,
			'medications' => $medications,
			'tracking_biometrics' => $biometrics_array
		];

		return view('wpUsers.patient.progress', $data);

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */

    public function u20(Request $request, $patientId = false)
	{

		$patient = User::find($patientId);
		$input = $request->all();

		if (isset($input['selectMonth'])) {
			$time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
			$start = $time->startOfMonth()->format('Y-m-d');
			$end = $time->endOfMonth()->format('Y-m-d');
			$month_selected = $time->format('m');
		} else {
			$time = Carbon::now();
			$start = Carbon::now()->startOfMonth()->format('Y-m-d');
			$end = Carbon::now()->endOfMonth()->format('Y-m-d');
			$month_selected = $time->format('m');
		}

		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

		$u20_patients = array();
		$billable_patients = array();

		// ROLLUP CATEGORIES
		$CarePlan = array('Edit/Modify Care Plan', 'Initial Care Plan Setup', 'Care Plan View/Print', 'Patient History Review', 'Patient Item Detail Review', 'Review Care Plan (offline)');
		$Progress = array('Review Patient Progress (offline)', 'Progress Report Review/Print');
		$RPM = array('Patient Alerts Review', 'Patient Overview Review', 'Biometrics Data Review', 'Lifestyle Data Review', 'Symptoms Data Review', 'Assessments Scores Review',
			'Medications Data Review', 'Input Observation');
		$TCM = array('Test (Scheduling, Communications, etc)', 'Transitional Care Management Activities', 'Call to Other Care Team Member', 'Appointments');
		$Other = array('other', 'Medication Reconciliation');
		$act_count = 0;
		foreach ($patients as $patient) {
			$monthly_time = intval($patient->getMonthlyTimeAttribute());
			if ($monthly_time < 1200 && $patient->role() == 'participant') {
				$u20_patients[$act_count]['colsum_careplan'] = 0;
				$u20_patients[$act_count]['colsum_changes'] = 0;
				$u20_patients[$act_count]['colsum_progress'] = 0;
				$u20_patients[$act_count]['colsum_rpm'] = 0;
				$u20_patients[$act_count]['colsum_tcc'] = 0;
				$u20_patients[$act_count]['colsum_other'] = 0;
				$u20_patients[$act_count]['colsum_total'] = 0;
				$u20_patients[$act_count]['ccm_status'] = $patient->getCCMStatus();
				$u20_patients[$act_count]['dob'] = $patient->DOB;
				$u20_patients[$act_count]['patient_name'] = $patient->getFullNameAttribute();
				$acts = DB::table('activities')
					->select(DB::raw('*,DATE(performed_at),provider_id, type'))
					->where('patient_id', $patient->ID)
					->whereBetween('performed_at', [
						$start, $end
					])
					->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
					->orderBy('performed_at', 'desc')
					->get();

//				foreach ($acts as $key => $value) {
//					$acts[$key]['patient'] = User::find($patient->ID);
//				}

				foreach ($acts as $activity) {
					if (in_array($activity->type, $CarePlan)) {
						$u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
					} else if (in_array($activity->type, $Progress)) {
						$u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
					} else if (in_array($activity->type, $RPM)) {
						$u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
					} else if (in_array($activity->type, $TCM)) {
						$u20_patients[$act_count]['colsum_tcc'] += intval($activity->duration);
					} else {
						$u20_patients[$act_count]['colsum_other'] += intval($activity->duration);
					}
					$u20_patients[$act_count]['colsum_total'] += intval($activity->duration);

				}
				$act_count++;
			}

		}

			$years = array();
			for ($i = 0; $i < 3; $i++) {
				$years[] = Carbon::now()->subYear($i)->year;
			}

			$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$act_data = true;
			if ($u20_patients == null) {
				$act_data = false;
			}

			$reportData = "data:" . json_encode($u20_patients) . "";
			debug(json_encode($u20_patients));
			$data = [
				'activity_json' => $reportData,
				'years' => array_reverse($years),
				'month_selected' => $month_selected,
				'months' => $months,
				'patient' => $patient,
				'data' => $act_data
			];
			//debug($reportData);

			return view('reports.u20', $data);
		}
	public function billing(Request $request, $patientId = false)
	{

		$patient = User::find($patientId);
		$input = $request->all();

		if (isset($input['selectMonth'])) {
			$time = Carbon::createFromDate($input['selectYear'], $input['selectMonth'], 15);
			$start = $time->startOfMonth()->format('Y-m-d');
			$end = $time->endOfMonth()->format('Y-m-d');
			$month_selected = $time->format('m');
		} else {
			$time = Carbon::now();
			$start = Carbon::now()->startOfMonth()->format('Y-m-d');
			$end = Carbon::now()->endOfMonth()->format('Y-m-d');
			$month_selected = $time->format('m');
		}

		$patients = User::whereIn('ID', Auth::user()->viewablePatientIds())->get();

		$u20_patients = array();
		$billable_patients = array();

		// ROLLUP CATEGORIES
		$CarePlan = array('Edit/Modify Care Plan', 'Initial Care Plan Setup', 'Care Plan View/Print', 'Patient History Review', 'Patient Item Detail Review', 'Review Care Plan (offline)');
		$Progress = array('Review Patient Progress (offline)', 'Progress Report Review/Print');
		$RPM = array('Patient Alerts Review', 'Patient Overview Review', 'Biometrics Data Review', 'Lifestyle Data Review', 'Symptoms Data Review', 'Assessments Scores Review',
			'Medications Data Review', 'Input Observation');
		$TCM = array('Test (Scheduling, Communications, etc)', 'Transitional Care Management Activities', 'Call to Other Care Team Member', 'Appointments');
		$Other = array('other', 'Medication Reconciliation');
		$act_count = 0;
		foreach ($patients as $patient) {
			$monthly_time = intval($patient->getMonthlyTimeAttribute());
			if ($monthly_time >= 1200 && $patient->role() == 'participant') {
				$u20_patients[$act_count]['colsum_careplan'] = 0;
				$u20_patients[$act_count]['colsum_changes'] = 0;
				$u20_patients[$act_count]['colsum_progress'] = 0;
				$u20_patients[$act_count]['colsum_rpm'] = 0;
				$u20_patients[$act_count]['colsum_tcc'] = 0;
				$u20_patients[$act_count]['colsum_other'] = 0;
				$u20_patients[$act_count]['colsum_total'] = 0;
				$u20_patients[$act_count]['ccm_status'] = $patient->getCCMStatus();
				$u20_patients[$act_count]['dob'] = $patient->DOB;
				$u20_patients[$act_count]['patient_name'] = $patient->getFullNameAttribute();
				$acts = DB::table('activities')
					->select(DB::raw('*,DATE(performed_at),provider_id, type'))
					->where('patient_id', $patient->ID)
					->whereBetween('performed_at', [
						$start, $end
					])
					->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
					->orderBy('performed_at', 'desc')
					->get();

//				foreach ($acts as $key => $value) {
//					$acts[$key]['patient'] = User::find($patient->ID);
//				}

				foreach ($acts as $activity) {
					if (in_array($activity->type, $CarePlan)) {
						$u20_patients[$act_count]['colsum_careplan'] += intval($activity->duration);
					} else if (in_array($activity->type, $Progress)) {
						$u20_patients[$act_count]['colsum_progress'] += intval($activity->duration);
					} else if (in_array($activity->type, $RPM)) {
						$u20_patients[$act_count]['colsum_rpm'] += intval($activity->duration);
					} else if (in_array($activity->type, $TCM)) {
						$u20_patients[$act_count]['colsum_tcc'] += intval($activity->duration);
					} else {
						$u20_patients[$act_count]['colsum_other'] += intval($activity->duration);
					}
					$u20_patients[$act_count]['colsum_total'] += intval($activity->duration);

				}
				$act_count++;
			}

		}

		$years = array();
		for ($i = 0; $i < 3; $i++) {
			$years[] = Carbon::now()->subYear($i)->year;
		}

		$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$act_data = true;
		if ($u20_patients == null) {
			$act_data = false;
		}

		$reportData = "data:" . json_encode($u20_patients) . "";
		debug(json_encode($u20_patients));
		$data = [
			'activity_json' => $reportData,
			'years' => array_reverse($years),
			'month_selected' => $month_selected,
			'months' => $months,
			'patient' => $patient,
			'data' => $act_data
		];
		//debug($reportData);

		return view('reports.u20', $data);
	}

	public function progress(Request $request, $id = false)
	{
		if ( $request->header('Client') == 'mobi' ) {
			// get and validate current user
			\JWTAuth::setIdentifier('ID');
			$wpUser = \JWTAuth::parseToken()->authenticate();
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} else {
			// get user
			$wpUser = User::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		$progressReport = new ReportsService();
		$feed = $progressReport->progress($wpUser->ID);

		return json_encode($feed);
	}

	public function careplan(Request $request, $id = false)
	{
		if ( $request->header('Client') == 'mobi' ) {
			// get and validate current user
			\JWTAuth::setIdentifier('ID');
			$wpUser = \JWTAuth::parseToken()->authenticate();
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} else {
			// get user
			$wpUser = User::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		$progressReport = new ReportsService();
		$feed = $progressReport->careplan($wpUser->ID);

		return response()->json($feed);
	}

	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
