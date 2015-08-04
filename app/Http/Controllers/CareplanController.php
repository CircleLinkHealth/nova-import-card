<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\WpUser;
use App\Observation;
use App\WpUserMeta;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CareplanController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
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
	public function show(Request $request, $id = false)
	{
		if ( $request->header('Client') == 'mobi' ) {
			// get and validate current user
			\JWTAuth::setIdentifier('ID');
			$user = \JWTAuth::parseToken()->authenticate();
			if (!$user) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} else {
			// get user
			$wpUser = WpUser::find($id);
			if(!$wpUser) {
				return response("User not found", 401);
			}
			if(!$wpUser->blogId()) {
				return response("User blog not found", 401);
			}

			// start feed
			$feed = array(
				"User_ID" => $id,
				"Comments" => "All data string are variable, DMS quantity and type of messages will change daily for each patient. Messages with Return Responses can nest. Message Content will have variable fields filled in by CPM and can vary between each patient. Message quantities will vary from day to day.",
				"Data" => array(
					"Version" => "2.1",
					"EventDateTime" => "2015-04-21T15:22:00.472Z"),
    			"CP_Feed" => array(),
			);

			// DMS
			// get date
			$dates = array('2015-05-15', '2015-05-14', '2015-05-13');
			if(empty($dates)) {
				return response("Date array is required", 401);
			}

			$i = 0;
			foreach($dates as $date) {
				$feed["CP_Feed"][$i] = array(
					"Feed" => array(
						"FeedDate" => $date,
						"DSM" => array(),
						"Reminders" => array(),
						"Biometric" => array(),
						"Symptoms" => array())
				);

				// DSM
				$query = Observation::select('ma_'.$wpUser->blogId().'_observations.*', 'rules_questions.*', 'rules_items.*', 'imsms.meta_value AS sms_en', 'imapp.meta_value AS app_en', 'comment_parent')
					->join('rules_questions', 'rules_questions.msg_id', '=', 'ma_'.$wpUser->blogId().'_observations.obs_message_id')
					->join('rules_items', 'rules_items.qid', '=', 'rules_questions.qid')
					->join('rules_itemmeta as imsms', function($join)
					{
						$join->on('imsms.items_id', '=', 'rules_items.items_id')->where('imsms.meta_key', '=', 'SMS_EN');
					})
					->leftJoin('rules_itemmeta as imapp', function($join)
					{
						$join->on('imapp.items_id', '=', 'rules_items.items_id')->where('imapp.meta_key', '=', 'APP_EN');
					})
					->join('rules_pcp', 'rules_pcp.pcp_id', '=', 'rules_items.pcp_id')
					->join('wp_'.$wpUser->blogId().'_comments', 'wp_'.$wpUser->blogId().'_comments.comment_id', '=', 'ma_'.$wpUser->blogId().'_observations.comment_id')
					->where('ma_'.$wpUser->blogId().'_observations.user_id', '=', $id)
					->where('obs_unit', '=', 'scheduled')
					->where('prov_id', '=', $wpUser->blogId())
					->whereRaw("obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
					->take(40);
				$scheduledObs = $query->get();
				if (!$scheduledObs->isEmpty()) {
					$d=0;
					foreach($scheduledObs as $obs) {
						// add to feed
						$feed["CP_Feed"][$i]['Feed']["DSM"][$d] = array(
							"MessageID" => $obs->obs_message_id,
							"Obs_Key" => $obs->obs_key,
							"ParentID" => $obs->comment_parent,
							"MesageIcon" => "question",
							"MessageContent" => $obs->app_en,
							"ReturnFieldType" => "None",
							"ReturnDataRangeLow" => null,
							"ReturnDataRangeHigh" => null,
							"ReturnValidAnswers" => null,
							"PatientAnswer" => null,
							"ResponseDTS" => null
						);

						// check for PatientAnswer and ResponseDTS
						$query = Observation::select('o.obs_id', 'o.obs_key', 'o.comment_id', 'o.obs_date', 'o.user_id', 'o.obs_value', 'o.obs_unit', 'o.obs_method', 'o.obs_message_id')
							->from('ma_'.$wpUser->blogId().'_observations AS o')
							->join('wp_'.$wpUser->blogId().'_comments AS cm', 'o.comment_id', '=', 'cm.comment_id')
							->where('o.user_id', "=", $id)
							->where('o.obs_key', "=", $obs->obs_key)
							->where('o.obs_message_id', "=", $obs->obs_message_id)
							->where('o.obs_unit', "!=", 'invalid')
							->where('o.obs_unit', "!=", 'scheduled')
							->whereRaw("o.obs_date BETWEEN '" . $date . " 00:00:00' AND '" . $date . " 23:59:59'", array())
							->orderBy("o.obs_date", "desc");
						$answerObs = $query->first();
						if ($answerObs) {
							$feed["CP_Feed"][$i]['Feed']["DSM"][$d]['PatientAnswer'] = $answerObs->obs_value;
							$feed["CP_Feed"][$i]['Feed']["DSM"][$d]['ResponseDTS'] = $answerObs->obs_date->format('Y-m-d H:i:s');
						}
						$d++;
					}
				}

				// Reminders

				// Biometric

				// Symptoms
				$i++;
			}




            // Dummy JSON Data for careplan
            //$str_data = json_decode(file_get_contents(getenv('CAREPLAN_JSON_PATH')));
            return response()->json($feed);
        }
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
