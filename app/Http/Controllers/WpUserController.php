<?php namespace App\Http\Controllers;

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

class WpUserController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' )
		{
			$userId = Crypt::decrypt($request->header('UserId'));

			$wpUsers = (new WpUser())->getWpUsersWithMeta($userId);

			return response()->json( Crypt::encrypt( json_encode( $wpUsers ) ) );

		} else if ( $request->header('Client') == 'mobi' ) {
			$response = [
				'user' => []
			];
			$statusCode = 200;

			\JWTAuth::setIdentifier('ID');
			$user = \JWTAuth::parseToken()->authenticate();
			if(!$user) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			} else {
				$userId = $user->ID;
				$wpUser = (new WpUser())->getWpUserWithMeta($userId);
				$response = [
					'id' => $wpUser->ID,
					'user_email' => $wpUser->user_email,
					'user_registered' => $wpUser->user_registered,
					'meta' => $wpUser->meta
				];
				return response()->json( $response, $statusCode );
			}
		} else {
			// display view
			$wpUsers = wpUser::orderBy('ID', 'desc')->limit(500);

			// FILTERS
			$params = $request->input();

			// role
			$roles = Role::all()->lists('display_name', 'id');
			$roleFilter = 'all';
			if(isset($params['action'])) {
				if(!empty($params['roleFilter'])) {
					$roleFilter = $params['roleFilter'];
					if($params['roleFilter'] != 'all') {
						$wpUsers = $wpUsers->whereHas('roles', function($q) use ($roleFilter){
							$q->where('id', '=', $roleFilter);
						});
					}
				}
				/*
				$params['programFilter'] = 7;
				if(!empty($params['programFilter'])) {
					$programFilter = $params['programFilter'];
					if($params['programFilter'] != 'all') {
						$wpUsers = $wpUsers->whereHas('roles', function($q) use ($programFilter){
							$q->where('id', '=', $programFilter);
						});
					}
				}
				*/
			}

			// only let owners see owners
			if(!Auth::user()->hasRole(['super-admin'])) {
				$wpUsers = $wpUsers->whereHas('super-admin', function ($q) use ($roleFilter) {
					$q->where('name', '!=', 'owner');
				});
			}


			$wpUsers = $wpUsers->get();
			$invalidUsers = array();
			$validUsers = array();
			foreach($wpUsers as $wpUser) {
				$userMeta = $wpUser->userMeta();

				if(empty($userMeta['user_config'])) {
					$invalidUsers[] = $wpUser;
					continue 1;
				}

				$validUsers[] = $wpUser;
			}
			return view('wpUsers.index', [ 'wpUsers' => $validUsers, 'roles' => $roles, 'roleFilter' => $roleFilter, 'invalidWpUsers' => $invalidUsers ]);
		}

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//$result = (new WpUser())->createNewUser('test@test.com', 'testpassword');
		//dd($result);

		// States (for dropdown)
		$states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");

		// programs for dd
		$wpBlogs = WpBlog::orderBy('blog_id', 'desc')->lists('domain', 'blog_id');

		// timezones for dd
		$timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($timezones_raw as $timezone) {
			$timezones_arr[$timezone] = $timezone;
		}
		// display view
		return view('wpUsers.create', ['states_arr' => $states_arr, 'timezones_arr' => $timezones_arr, 'wpBlogs' => $wpBlogs]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$params = $request->input();

		// instantiate new user
		$wpUser = new WpUser;

		// validate
		$this->validate($request, $wpUser->rules);

		// create user
		$wpUser->createNewUser($params['user_email'], $params['user_pass']);

		// instantiate user with meta
		$wpUser = WpUser::with('meta')->find($wpUser->ID);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'first_name';
		$userMeta->meta_value = $params['first_name'];
		$wpUser->meta()->save($userMeta);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'last_name';
		$userMeta->meta_value = $params['last_name'];
		$wpUser->meta()->save($userMeta);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'nickname';
		$userMeta->meta_value = $params['nickname'];
		$wpUser->meta()->save($userMeta);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'description';
		$userMeta->meta_value = 'lv generated user';
		$wpUser->meta()->save($userMeta);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'primary_blog';
		$userMeta->meta_value = $params['primary_blog'];
		$wpUser->meta()->save($userMeta);

		$userMeta = new WpUserMeta;
		$userMeta->user_id = $wpUser->ID;
		$userMeta->meta_key = 'wp_'.$params['primary_blog'].'_capabilities';
		$userMeta->meta_value = serialize(array());
		$wpUser->meta()->save($userMeta);

		$wpUser->push();
		return redirect()->route('usersEdit', [$wpUser->ID])->with('messages', ['successfully created new user - '.$wpUser->ID]);

		dd('created new user id '.$wpUser->ID);

		// created base user, add user info

		// add user meta








		// get user
		$wpUser = WpUser::with('meta')->find($id);

		// usermeta first_name
		$userMeta = $wpUser->meta->where('meta_key', 'first_name')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('first_name');
			$userMeta->save();
		}

		// usermeta last_name
		$userMeta = $wpUser->meta->where('meta_key', 'last_name')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('last_name');
			$userMeta->save();
		}

		// usermeta nickname
		$userMeta = $wpUser->meta->where('meta_key', 'nickname')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('nickname');
			$userMeta->save();
		}

		// usermeta description
		$userMeta = $wpUser->meta->where('meta_key', 'description')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('description');
			$userMeta->save();
		}

		// get user meta primary_blog
		$userMeta = $wpUser->meta->lists('meta_value', 'meta_key');
		$primaryBlog = $userMeta['primary_blog'];

		// update role
		$input = $request->input('role');
		if(!empty($input)) {
			$capabilities = $wpUser->meta->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_capabilities')->first();
			if($capabilities) {
				$capabilities->meta_value = serialize(array($input => '1'));
			} else {
				$capabilities = new WpUserMeta;
				$capabilities->meta_key = 'wp_' . $primaryBlog . '_capabilities';
				$capabilities->meta_value = serialize(array($input => '1'));
				$capabilities->user_id = $id;
			}
			$capabilities->save();
		}

		// update user config
		$userConfigTemplate = $wpUser->userConfigTemplate();
		foreach($userConfigTemplate as $key => $value) {
			$input = $request->input($key);
			if(!empty($input)) {
				$userConfigTemplate[$key] = $request->input($key);
			}
		}
		$userConfig = $wpUser->meta->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_user_config')->first();
		if($userConfig) {
			$userConfig->meta_value = serialize($userConfigTemplate);
		} else {
			$userConfig = new WpUserMeta;
			$userConfig->meta_key = 'wp_' . $primaryBlog . '_user_config';
			$userConfig->meta_value = serialize($userConfigTemplate);
			$userConfig->user_id = $id;
		}
		$userConfig->save();
		//dd($userConfigTemplate);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request, $id)
	{
		dd('user /edit to view user info');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(Request $request, $id)
	{
		$messages = \Session::get('messages');

		$wpUser = WpUser::find($id);
		if(!$wpUser) {
			return response("User not found", 401);
		}

		$roles = Role::all();

		// primary_blog
		$userMeta = WpUserMeta::where('user_id', '=', $id)->lists('meta_value', 'meta_key');
		if(!isset($userMeta['primary_blog'])) {
			return response("Required meta primary_blog not found", 401);
		}
		$primaryBlog = $userMeta['primary_blog'];

		$params = $request->all();
		if(!empty($params)) {
			if (isset($params['action'])) {
				if ($params['action'] == 'impersonate') {
					Auth::login($id);
					return redirect()->route('/', [])->with('messages', ['Logged in as user '.$id]);
				}
			}
		}

		// user config
		$userConfig = $wpUser->userConfigTemplate();
		if(isset($userMeta['wp_' . $primaryBlog . '_user_config'])) {
			$userConfig = unserialize($userMeta['wp_' . $primaryBlog . '_user_config']);
			$userConfig = array_merge($wpUser->userConfigTemplate(), $userConfig);
		}

		// set role
		$capabilities = unserialize($userMeta['wp_' . $primaryBlog . '_capabilities']);
		$wpRole = key($capabilities);

		// locations @todo get location id for WpBlog
		$wpBlog = WpBlog::find($primaryBlog);
		$locations_arr = (new Location)->getNonRootLocations($wpBlog->locationId());

		// States (for dropdown)
		$states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");

		// programs for dd
		$wpBlogs = WpBlog::orderBy('blog_id', 'desc')->lists('domain', 'blog_id');

		// timezones for dd
		$timezones_raw = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
		foreach($timezones_raw as $timezone) {
			$timezones_arr[$timezone] = $timezone;
		}

		// providers
		$providers_arr = array('provider' => 'provider', 'office_admin' => 'office_admin', 'participant' => 'participant', 'care_center' => 'care_center', 'viewer' => 'viewer', 'clh_participant' => 'clh_participant', 'clh_administrator' => 'clh_administrator');

		// display view
		return view('wpUsers.edit', ['wpUser' => $wpUser, 'locations_arr' => $locations_arr, 'states_arr' => $states_arr, 'timezones_arr' => $timezones_arr, 'wpBlogs' => $wpBlogs, 'userConfig' => $userConfig, 'userMeta' => $userMeta, 'primaryBlog' => $primaryBlog, 'wpRole' => $wpRole, 'providers_arr' => $providers_arr, 'messages' => $messages, 'roles' => $roles]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		// get user
		$wpUser = WpUser::with('meta')->find($id);

		$params = $request->input();

		if(isset($params['roles'])) {
			$wpUser->roles()->sync($params['roles']);
		} else {
			$wpUser->roles()->sync(array());
		}

		// usermeta first_name
		$userMeta = $wpUser->meta()->where('meta_key', 'first_name')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('first_name');
			$userMeta->save();
		}

		// usermeta last_name
		$userMeta = $wpUser->meta()->where('meta_key', 'last_name')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('last_name');
			$userMeta->save();
		}

		// usermeta nickname
		$userMeta = $wpUser->meta()->where('meta_key', 'nickname')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('nickname');
			$userMeta->save();
		}

		// usermeta description
		$userMeta = $wpUser->meta()->where('meta_key', 'description')->first();
		if($userMeta) {
			$userMeta->meta_value = $request->input('description');
			$userMeta->save();
		}

		// get user meta primary_blog
		$userMeta = $wpUser->meta()->lists('meta_value', 'meta_key');
		$primaryBlog = $userMeta['primary_blog'];

		// update role
		$input = $request->input('role');
		if(!empty($input)) {
			$capabilities = $wpUser->meta()->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_capabilities')->first();
			if($capabilities) {
				$capabilities->meta_value = serialize(array($input => '1'));
			} else {
				$capabilities = new WpUserMeta;
				$capabilities->meta_key = 'wp_' . $primaryBlog . '_capabilities';
				$capabilities->meta_value = serialize(array($input => '1'));
				$capabilities->user_id = $id;
			}
			$capabilities->save();
		}

		// update user config
		$userConfigTemplate = $wpUser->userConfigTemplate();
		foreach($userConfigTemplate as $key => $value) {
			$input = $request->input($key);
			if(!empty($input)) {
				$userConfigTemplate[$key] = $request->input($key);
			}
		}
		$userConfig = $wpUser->meta()->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_user_config')->first();
		if($userConfig) {
			$userConfig->meta_value = serialize($userConfigTemplate);
		} else {
			$userConfig = new WpUserMeta;
			$userConfig->meta_key = 'wp_' . $primaryBlog . '_user_config';
			$userConfig->meta_value = serialize($userConfigTemplate);
			$userConfig->user_id = $id;
		}
		$userConfig->save();
		//dd($userConfigTemplate);

		return redirect()->back()->with('messages', ['successfully updated user']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 *
	 */
	public function destroy($id)
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showMsgCenter(Request $request, $id)
	{
		$msgUI = new MsgUI;
		$msgUsers = new MsgUser;
		$msgChooser = new MsgChooser;
		$msgScheduler = new MsgScheduler;
		$observationService = new ObservationService;
		//$result = $msgChooser->setNextMessage($id, 28715, 'CF_HSP_20', 'C', 'HSP');
		//$result = $msgChooser->setNextMessage($id, 28715, 'CF_RPT_50', '7', 'RPT'); // bad
		//$result = $msgChooser->setNextMessage($id, 28715, 'CF_RPT_50', '0', 'RPT'); // great
		//$result = $msgChooser->setNextMessage($id, 28715, 'CF_RPT_40', '175', 'RPT');
		//$result = $msgChooser->setNextMessage($id, 28715, 'CF_REM_NAG_01', '', 'RPT');
		//$result = $msgChooser->setNextMessage($id, 29311, 'CF_SOL_MED_BT', 'Y', 'RPT');
		//dd($result);
		$wpUser = WpUser::find($id);
		if(!$wpUser) {
			return response("User not found", 401);
		}
		$params = $request->input();
		$userMeta = $wpUser->userMeta();

		$messageKey = '';
		$messageValue = '';
		$activeDate = ''; // keeps date section open
		if(!empty($params)) {
			if(isset($params['action'])) {
				if($params['action'] == 'sendTextSimulation') {
					/*
					// send text
					$api = $wpUser->blogId();
					$msgid = 'xOx';
					$phone = $userMeta['user_config']['study_phone_number'];
					$msg = 'Some Text Responseee';
					$msg = str_replace("'", "''", $msg);
					// $msg = preg_replace('/[^0-9a-zA-Z \/]/', ' ', urldecode($msg));
					$msg = preg_replace("/[_]/", "", urldecode($msg)); // remove underscores as they will be used to replace forward slashes
					$msg = preg_replace("/[\/]/", '_', $msg); // change forward slashes to underscores
					$msg = preg_replace("/[^0-9a-zA-Z _]/", '', $msg);  // remove all non-alphanumeric characters

					// public function getInboundStream($intBlogId, $hexMoMsgId, $strPhoneNumber, $strResponseMessage)
					$inboundsms = 'MsgReceiver->getInboundStream(/'.$msgid.'/'.$phone.'/'.str_replace(array(' ',','),array('%20',''),$msg) . ')';

					dd($inboundsms);
					return redirect()->back()->with('messages', ['successfully did something']);
					*/
				} else if($params['action'] == 'run_scheduler') {
					$result = $msgScheduler->index($wpUser->blogId());
					return response()->json($result);
				} else if($params['action'] == 'save_app_obs') {
					$result = $observationService->storeObservationFromApp($id, $params['parent_id'], $params['obs_value'], $params['obs_date'], $params['msg_id'], $params['obs_key'], 'America/New_York');
					// create message
					if($result) {
						$messageKey = 'success';
						$messageValue = 'Successfully saved new app observation.';
					} else {
						$messageKey = 'error';
						$messageValue = 'Failed to save app observation.';
					}
					// add param to keep date section open
					$date = strtotime($params['obs_date']);
					$activeDate = date('Y-m-d', $date);
				}
			}
		}

		$commentsForUser = $msgUsers->get_comments_for_user($wpUser->ID, $wpUser->blogId());
		$comments = array();
		if(!empty($commentsForUser)) {
			foreach($commentsForUser as $comment) {
				$comments[$comment->comment_ID] = array(
					'comment_type' => $comment->comment_type,
					'comment_author' => $comment->comment_author,
					'comment_date' => $comment->comment_date,
					'comment_approved' => $comment->comment_approved,
					'comment_parent' => $comment->comment_parent,
					'comment_content' => $comment->comment_content,
					'comment_content_array' => unserialize($comment->comment_content),
				);
			}
		}

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
		$cpFeed = $careplanService->getCareplan($wpUser, $dates);
		//$cpFeed = json_decode(file_get_contents(getenv('CAREPLAN_JSON_PATH')), 1);
		$cpFeed = $msgUI->addAppSimCodeToCP($cpFeed);
		$cpFeedSections = array('Biometric', 'DMS', 'Symptoms', 'Reminders');

		//return response()->json($cpFeed);
		return view('wpUsers.msgCenter', ['wpUser' => $wpUser, 'userMeta' => $userMeta, 'cpFeed' => $cpFeed, 'cpFeedSections' => $cpFeedSections, 'comments' => $comments, 'messages' => array(), $messageKey => $messageValue, 'activeDate' => $activeDate]);
	}



	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function showPatientSummary(Request $request, $id)
	{
		$msgUI = new MsgUI;
		$msgUsers = new MsgUser;
		$msgChooser = new MsgChooser;
		$msgScheduler = new MsgScheduler;
		$observationService = new ObservationService;
		$wpUser = WpUser::find($id);
		if(!$wpUser) {
			return response("User not found", 401);
		}



		$sections = array(
			array('section' => 'obs_biometrics', 'id' => 'obs_biometrics_dtable', 'title' => 'Biometrics', 'col_name_question' => 'Reading Type', 'col_name_severity' => 'Reading'),
			array('section' => 'obs_medications', 'id' => 'obs_medications_dtable', 'title' => 'Medications', 'col_name_question' => 'Medication', 'col_name_severity' => 'Adherence'),
			array('section' => 'obs_symptoms', 'id' => 'obs_symptoms_dtable', 'title' => 'Symptoms', 'col_name_question' => 'Symptom', 'col_name_severity' => 'Severity'),
			array('section' => 'obs_lifestyle', 'id' => 'obs_lifestyle_dtable', 'title' => 'Lifestyle', 'col_name_question' => 'Question', 'col_name_severity' => 'Response'),
		);


		/*
		 *
		// set blog id
		$programId = $wpUser->blogId();
		$user_id = $wpUser->ID;

		$date_sort = 'desc';
		// observation_model->get_user_observations()

		// build tables to use
		$str_observation_table = 'ma_' . $programId . '_observations';
		$str_observationmeta_table  = 'ma_' . $programId . '_observationmeta';
		$str_comments_table  = 'wp_' . $programId . '_comments';

		$query = DB::connection('mysql_no_prefix')->select("o.obs_key, rq.qtype, o.obs_id, o.sequence_id, o.obs_date, o.comment_id, o.user_id, o.obs_value, o.obs_unit, o.obs_method, o.obs_message_id, cm.comment_date, om.meta_key, ri.items_text, rq.description, im_lvl.meta_value AS dm_alert_level, im_log.meta_value AS dm_log",false);
		$query->from($str_observation_table . ' AS o');
		$query->join($str_comments_table . ' AS cm', 'o.comment_id','=','cm.comment_id');
		$query->join('rules_questions AS rq', 'o.obs_message_id','=','rq.msg_id');
		$query->join($str_observationmeta_table . ' AS im_log', "im_log.obs_id",'=',"o.obs_id AND im_log.meta_key = 'dm_log'", 'left');
		$query->join($str_observationmeta_table . ' AS im_lvl', "im_lvl.obs_id",'=',"o.obs_id AND im_lvl.meta_key = 'dm_alert_level'", 'left');
		$query->join('rules_items AS ri', 'ri.qid','=','rq.qid');
		$query->join($str_observationmeta_table . ' AS om', 'o.obs_id','=','om.obs_id', 'left');
		$where = array('rq.qtype !=' => 'AnswerResponse');
		if($user_id) {
			$where = array('o.user_id' => $user_id, 'rq.qtype !=' => 'AnswerResponse');
		}
		$query->where($where);
		$query->where('o.obs_unit != "invalid"');
		$query->where('o.obs_unit != "scheduled"');
		$query->where('o.obs_key != ""');
		$query->group_by("obs_id");
		$query->order_by("comment_date", $date_sort);
		$query->order_by("obs_id", 'DESC');
		$result = $query->get();
		*/

		$observations = Observation::where('user_id' ,'=', $wpUser->ID);
		$observations->where('obs_unit' ,'!=', "invalid");
		$observations->where('obs_unit' ,'!=', "scheduled");
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
			$observation_json[$section] = "data:[";
			foreach ($observations as $observation) {
				// lastly format json
				$observation_json[$section] .= "{ obs_key:'" . $observation->obs_key . "', " .
					"description:'" . $observation->obs_value . "', " .
					"obs_value:'" . $observation->obs_value . "', " .
					"dm_alert_level:'default', " .
					"obs_unit:'" . $observation->obs_unit . "', " .
					"obs_message_id:'" . $observation->obs_message_id . "', " .
					"comment_date:'09-04-15 06:43:56', " . "},";
				/*
				$observation_data[] = array(
					'obs_key' => $observation->obs_key,
					'description' => $observation->obs_value,
					'obs_value' => $observation->obs_value,
					'dm_alert_level' => $observation->obs_value,
					'obs_unit' => $observation->obs_unit,
					'obs_message_id' => $observation->obs_message_id,
					'comment_date' => $observation->obs_date
				);
				*/
			}
			$observation_json[$section] .= "],";
		}

		//dd($observation_json['obs_biometrics']);

		/*
		// get observations for user
		//$observation_data = $this->observation_model->get_user_observations($user_id, $blog_id);

		// get user data
		//$user_data = $this->users_model->get_users_data($user_id, 'id', $blog_id);
		//$user_data_ucp = $user_data[$user_id]['usermeta']['user_care_plan'];

		// build array of pcp
		$obs_by_pcp = array(
			'obs_biometrics' => array(),
			'obs_medications' => array(),
			'obs_symptoms' => array(),
			'obs_lifestyle' => array(),
		);
		foreach($observation_data as $observation) {
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
		$observation_data = $obs_by_pcp;
		*/

		//return response()->json($cpFeed);
		return view('wpUsers.patient.summary', ['wpUser' => $wpUser, 'sections' => $sections, 'observation_data' => $observation_json]);
	}
}
