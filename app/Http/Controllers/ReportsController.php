<?php namespace App\Http\Controllers;

use App\Activity;
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

			$timeLessThan = 20;
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

            $data = DB::table('activities')
                ->select(DB::raw('*,DATE(performed_at),provider_id, type, SUM(duration)'))
				->whereBetween('performed_at', [
					Carbon::createFromFormat('Y-n', $months[0])->startOfMonth(),
					Carbon::createFromFormat('Y-n', $months[1])->endOfMonth()
				])
                ->where('patient_id', $patients[0])
                ->groupBy(DB::raw('provider_id, DATE(performed_at),type'))
                ->orderBy('performed_at', 'desc')
                ->get();

            $data = json_decode(json_encode($data), true);

            foreach($data as $key => $value){
                $data[$key]['patient'] = WpUser::find($patients[0]);
            }

            $data1 = array();
            $data1[$patients[0]] = $data;

            foreach($patients as $patientId) {
                $reportData[$patientId] = array();
            }
            foreach ($data1 as $patientAct)
            {
                $reportData[$patientAct[0]['patient_id']] = collect($patientAct)->groupBy('performed_at_year_month');
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
