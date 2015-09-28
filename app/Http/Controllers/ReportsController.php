<?php namespace App\Http\Controllers;

use App\Activity;
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
	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' )
		{
			$months = Crypt::decrypt($request->header('months'));

			$patients = [];
			if( !empty( $request->header('patients') ) ) {
				$patients = Crypt::decrypt($request->header('patients'));
			};

			$range = true;
			if($request->header('range')) {
				$range = Crypt::decrypt($request->header('range'));
			};

			$timeLessThan = 1200;
			if( !empty( $request->header('timeLessThan') ) ) {
				$timeLessThan = Crypt::decrypt($request->header('timeLessThan'));
			};

			$reportData = Activity::getReportData($months,$timeLessThan,$patients,$range);

			if(!empty($reportData)) {
				return response()->json(Crypt::encrypt(json_encode($reportData)));
			} else {
				return response('Not Found', 204);
			}
		}
		return response('Unauthorized', 401);
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

		// Dummy JSON Data for careplan
		//$str_data = json_decode(file_get_contents(getenv('REPORT_PROGRESS_JSON_PATH')));
		//return response()->json($str_data);

		$progressReport = new ReportsService();
		$feed = $progressReport->progress($wpUser->ID);

		response()->json($feed);
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

		// Dummy JSON Data for careplan
		$str_data = json_decode(file_get_contents(getenv('REPORT_CAREPLAN_JSON_PATH')));
		return response()->json($str_data);

		// get dates
		$date1 = date('Y-m-d');
		$date2 = date('Y-m-d', time() - 60 * 60 * 24);
		$date3 = date('Y-m-d', time() - ((60 * 60 * 24) * 2));
		$dates = array($date1, $date2, $date3);
		if(empty($dates)) {
			return response("Date array is required", 401);
		}

		// get feed
		$careplanService = new CareplanService;
		$feed = $careplanService->getCareplan($wpUser, $dates);

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
