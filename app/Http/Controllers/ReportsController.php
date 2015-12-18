<?php namespace App\Http\Controllers;

use App\Activity;
use App\CPRulesItem;
use App\CPRulesPCP;
use App\CPRulesUCP;
use App\Services\CareplanService;
use App\Services\ReportsService;
use App\WpUser;
use Carbon\Carbon;
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
		$user = WpUser::find($patientId);

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
			$bio_name = $key;
			$first = reset($value);
			$last = end($value);
			$biometrics_array[$bio_name]['change'] = intval($last->Avg) - intval($first->Avg);
			$biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);

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
		debug($biometrics_array);


		//Medication Tracking:
		$medications = (new ReportsService())->medicationStatus($user);

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

    public function pageTimerReports(Request $request){

        if ( $request->header('Client') == 'ui' )
        {
            $patients = [];
            if( !empty( $request->header('patients') ) ) {
                $patients = Crypt::decrypt($request->header('patients'));
            };

			$months = Crypt::decrypt($request->header('months'));

            $acts = DB::table('activities')
                ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration)'))
				->whereBetween('performed_at', [
					Carbon::createFromFormat('Y-n', $months[0])->startOfMonth(),
					Carbon::createFromFormat('Y-n', $months[1])->endOfMonth()
				])
                ->where('patient_id', $patients[0])
                ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                ->orderBy('performed_at', 'desc')
                ->get();

            $acts = json_decode(json_encode($acts), true);

            foreach($acts as $key => $value){
                $acts[$key]['patient'] = WpUser::find($patients[0]);
            }

			foreach($acts as $key => $value){
				$act_id = $acts[$key]['id'];
				$acts_ = Activity::find($act_id);
				$comment = $acts_->getActivityCommentFromMeta($act_id);
				$acts[$key]['comment'] = $comment;
			}

            $activities_data_with_users = array();
            $activities_data_with_users[$patients[0]] = $acts;

            foreach($patients as $patientId) {
                $reportData[$patientId] = array();
            }
            foreach ($activities_data_with_users as $patientAct)
            {
                $reportData[$patientAct[0]['patient_id']] = collect($patientAct)->groupBy('performed_at_year_month');
				//$reportData[$patientAct[0]['patient_id']]getActivityCommentFromMeta($id)
            }

            if(!empty($reportData)) {
               return response()->json(Crypt::encrypt(json_encode($reportData)));
				//return response($months, 201);
            } else {
                return response('Not Found', 204);
            }
        }
        return response('Unauthorized', 401);
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
			$wpUser = WpUser::find($id);
			if (!$wpUser) {
				return response("User not found", 401);
			}
		}

		$progressReport = new ReportsService();
		$feed = $progressReport->progress($wpUser->ID);

		return json_encode($feed);
	}

	public function UIprogress(Request $request, $id){
		if ( $request->header('Client') == 'ui' ){ // WP Site
			$progressReport = new ReportsService();
			$feed = $progressReport->progress($id);
			$response['body'] = $feed;
			return response()->json(Crypt::encrypt($response, ['message' => 'OK']), 201);
		} else {
			return response()->json(Crypt::encrypt(['error' => 'Fail']), 401);
		}
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
			$wpUser = WpUser::find($id);
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
