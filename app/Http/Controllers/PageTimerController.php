<?php namespace App\Http\Controllers;

use App\Activity;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\PageTimer;
use App\Rules;
use App\WpUser;

use Illuminate\Http\Request;

class PageTimerController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//$this->addPageTimerActivities(array(378));
		// display view
		$pageTimes = PageTimer::orderBy('id', 'desc')->take(50)->get();
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

		$data = $request->only('totalTime',
			'patientId',
			'providerId',
			'programId',
			'startTime',
			'urlFull',
			'urlShort',
			'ipAddr',
			'activity',
			'title',
			'qs');;

		//echo $data['totalTime']; die();
		if(!isset($data['totalTime'])
			|| !isset($data['patientId'])
			|| !isset($data['providerId'])
			|| !isset($data['programId'])
			|| !isset($data['startTime'])
			|| !isset($data['urlFull'])
			|| !isset($data['urlShort'])
			|| !isset($data['ipAddr'])
			|| !isset($data['activity'])
			|| !isset($data['title'])
			|| !isset($data['qs'])
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
		$pagetimer->activity_type = $data['activity'];
		$pagetimer->title = $data['title'];
		$pagetimer->query_string = $data['qs'];
		$pagetimer->save();

		$this->addPageTimerActivities(array($pagetimer->id));

		return response("PageTimer Logged, duration:". $data['totalTime'], 201);
	}


	public function addPageTimerActivities($page_timer_ids = array()) {
		if(!empty($page_timer_ids)) {
			foreach($page_timer_ids as $page_timer_id) {
				// first get page timer params
				$pageTime = PageTimer::where('id', '=', $page_timer_id)->first();
				if(!$pageTime) {
					continue 1;
				}

				// check params to see if rule exists
				$params = array();
				$provider = WpUser::find( $pageTime->provider_id );
				$params['role'] = $provider->role();
				$providerMeta = $provider->meta;
				$params['activity'] = $pageTime->activity_type;
				$params['role'] = $provider->role();
				$params['program_id'] = $pageTime->program_id;
				//$params = array('role' => 'Provider', 'activity' => 'Patient Overview');

				// check against rules and add activity if passes
				$rules = new Rules;
				//dd( $rules->getActions(array(), 'ATT') );
				$ruleActions = $rules->getActions($params, 'ATT');
				//dd($ruleActions);

				if($ruleActions) {
					$activiyParams = array();
					$activiyParams['type'] = $params['activity'];
					$activiyParams['provider_id'] = $pageTime->provider_id;
					$activiyParams['performed_at'] = $pageTime->start_time;
					$activiyParams['duration'] = ceil($pageTime->duration / 60);
					$activiyParams['duration_unit'] = 'minutes';
					$activiyParams['patient_id'] = $pageTime->patient_id;
					$activiyParams['logged_from'] = 'pagetimer';
					$activiyParams['logger_id'] = $pageTime->provider_id;
					$activiyParams['page_timer_id'] = $pageTime->id;
					$activiyParams['meta'] = array('meta_key' => 'comment', 'meta_value' => 'logged from pagetimer');

					// if rule exists, create activity
					$result = Activity::createNewActivity($activiyParams);
				}
				return true;
			}
		}
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
