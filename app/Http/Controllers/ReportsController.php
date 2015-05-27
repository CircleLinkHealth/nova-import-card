<?php namespace App\Http\Controllers;

use App\Activity;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
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
