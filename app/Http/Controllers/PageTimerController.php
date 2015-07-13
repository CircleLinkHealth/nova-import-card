<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\PageTimer;

use Illuminate\Http\Request;

class PageTimerController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// display view
		$pageTimes = PageTimer::orderBy('id', 'desc')->get();
		return view('pageTimer.index', [ 'pageTimes' => $pageTimes ]);
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
	public function store(Request $request)
	{

		$data = $request->only('totalTime', 'patientId', 'providerId', 'programId', 'startTime', 'urlFull', 'urlShort', 'ipAddr');;

		//echo $data['totalTime']; die();
		if(!isset($data['totalTime'])
			|| !isset($data['patientId'])
			|| !isset($data['providerId'])
			|| !isset($data['programId'])
			|| !isset($data['startTime'])
			|| !isset($data['urlFull'])
			|| !isset($data['urlShort'])
			|| !isset($data['ipAddr'])
		) {
			return response("missing required params", 201);
		}

		$pagetimer = new PageTimer();
		$pagetimer->duration = ($data['totalTime'] / 1000);
		$pagetimer->duration_unit = 'seconds';
		$pagetimer->patient_id = $data['patientId'];
		$pagetimer->provider_id = $data['providerId'];
		$pagetimer->start_time = $data['startTime'];
		date_default_timezone_set('America/New_York');
		$pagetimer->end_time = date('Y-m-d H:i:s');
		$pagetimer->url_full = $data['urlFull'];
		$pagetimer->url_short = $data['urlShort'];
		$pagetimer->program_id = $data['programId'];
		$pagetimer->ip_addr = $data['ipAddr'];
		$pagetimer->save();

		return response("PageTimer Logged, duration:". $data['totalTime'], 201);
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
