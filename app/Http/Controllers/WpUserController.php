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
			$wpUsers = wpUser::where('program_id', '!=', '')->orderBy('ID', 'desc');

			// FILTERS
			$params = $request->input();

			// role filter
			$roles = Role::all()->lists('display_name', 'id');
			$filterRole = 'all';
			if(!empty($params['filterRole'])) {
				$filterRole = $params['filterRole'];
				if($params['filterRole'] != 'all') {
					$wpUsers->whereHas('roles', function($q) use ($filterRole){
						$q->where('id', '=', $filterRole);
					});
				}
			}

			// program filter
			$programs = WpBlog::orderBy('blog_id', 'desc')->get()->lists('domain', 'blog_id');
			$filterProgram = 'all';
			if(!empty($params['filterProgram'])) {
				$filterProgram = $params['filterProgram'];
				if($params['filterProgram'] != 'all') {
					$wpUsers->where('program_id', '=', $filterProgram);
				}
			}

			// only let owners see owners
			if(!Auth::user()->hasRole(['administrator'])) {
				$wpUsers = $wpUsers->whereHas('roles', function ($q) {
					$q->where('name', '!=', 'administrator');
				});
				// providers can only see their patients
				if(Auth::user()->hasRole(['provider'])) {
					$wpUsers->whereHas('roles', function ($q) {
						$q->where('name', '=', 'patient');
					});
					$wpUsers->where('program_id', '=', Auth::user()->program_id);
				}
			}

			$wpUsers = $wpUsers->paginate(20);
			$invalidUsers = array();
			return view('wpUsers.index', [ 'wpUsers' => $wpUsers, 'programs' => $programs, 'filterProgram' => $filterProgram, 'roles' => $roles, 'filterRole' => $filterRole, 'invalidWpUsers' => $invalidUsers ]);
		}

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$messages = \Session::get('messages');

		$wpUser = new WpUser;

		$roles = Role::all();

		// user config
		$userConfig = $wpUser->userConfigTemplate();

		// set role
		$wpRole = '';

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
		return view('wpUsers.create', ['wpUser' => $wpUser, 'states_arr' => $states_arr, 'timezones_arr' => $timezones_arr, 'wpBlogs' => $wpBlogs, 'userConfig' => $userConfig, 'wpRole' => $wpRole, 'providers_arr' => $providers_arr, 'messages' => $messages, 'roles' => $roles]);
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

		// the basics
		$wpUser->user_nicename = $params['user_nicename'];
		$wpUser->user_nicename = $params['user_nicename'];
		$wpUser->program_id = $params['primary_blog'];

		// lv roles
		if(isset($params['roles'])) {
			$wpUser->roles()->sync($params['roles']);
		} else {
			$wpUser->roles()->sync(array());
		}

		// save meta
		$userMetaTemplate = $wpUser->userMetaTemplate();
		foreach($userMetaTemplate as $key => $value) {
			$userMeta = new WpUserMeta;
			$userMeta->user_id = $wpUser->ID;
			$userMeta->meta_key = $key;
			$userMeta->meta_value = $request->input($key);
			$wpUser->meta()->save($userMeta);
		}

		// update role / capabilities (wp)
		$input = $request->input('role');
		$capabilities = new WpUserMeta;
		$capabilities->meta_key = 'wp_' . $params['primary_blog'] . '_capabilities';
		$capabilities->meta_value = serialize(array($input => '1'));
		$capabilities->user_id = $wpUser->ID;
		$capabilities->save();
		$capabilities = new WpUserMeta;
		$capabilities->meta_key = 'wp_' . $params['primary_blog'] . '_user_level';
		$capabilities->meta_value = '0';
		$capabilities->user_id = $wpUser->ID;
		$capabilities->save();

		// update user config
		$userConfigTemplate = $wpUser->userConfigTemplate();
		foreach($userConfigTemplate as $key => $value) {
			$input = $request->input($key);
			if(!empty($input)) {
				$userConfigTemplate[$key] = $request->input($key);
			}
		}
		$userConfig = $wpUser->meta->where('user_id', '=', $wpUser->ID)->where('meta_key', '=', 'wp_' . $params['primary_blog'] . '_user_config')->first();
		if($userConfig) {
			$userConfig->meta_value = serialize($userConfigTemplate);
		} else {
			$userConfig = new WpUserMeta;
			$userConfig->meta_key = 'wp_' . $params['primary_blog'] . '_user_config';
			$userConfig->meta_value = serialize($userConfigTemplate);
			$userConfig->user_id = $wpUser->ID;
		}
		$userConfig->save();

		$wpUser->push();
		return redirect()->route('users.edit', [$wpUser->ID])->with('messages', ['successfully created new user - '.$wpUser->ID]);

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
		// instantiate user
		$wpUser = WpUser::with('meta')->find($id);
		if (!$wpUser) {
			return response("User not found", 401);
		}

		// validate
		$roles = $request->input('roles');
		if(!empty($roles)) {
			foreach($roles as $roleId) {
				// get Role to check validation
				$role = Role::find($roleId);
				if ($role->name == 'patient') {
					$this->validate($request, $wpUser->patient_rules);
				}
			}
		}

		// return back
		//return redirect()->back()->withInput()->with('messages', ['successfully created/updated patient'])->send();

		// input
		$params = $request->all();

		// the basics
		$wpUser->user_nicename = $params['user_nicename'];
		$wpUser->display_name = $params['display_name'];
		$wpUser->primary_blog = $params['primary_blog'];
		$wpUser->save();

		// roles
		if(isset($roles)) {
			$wpUser->roles()->sync($roles);
		} else {
			$wpUser->roles()->sync(array());
		}

		// save meta
		$userMetaTemplate = $wpUser->userMetaTemplate();
		foreach($userMetaTemplate as $key => $value) {
			$userMeta = $wpUser->meta()->where('meta_key', $key)->first();
			if(!$userMeta) {
				$userMeta = new WpUserMeta;
			}
			$userMeta->user_id = $wpUser->ID;
			$userMeta->meta_key = $key;
			$userMeta->meta_value = $request->input($key);
			$wpUser->meta()->save($userMeta);
		}

		// update role
		$input = $request->input('role');
		if(!empty($input)) {
			$capabilities = $wpUser->meta()->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $wpUser->blogId() . '_capabilities')->first();
			if($capabilities) {
				$capabilities->meta_value = serialize(array($input => '1'));
			} else {
				$capabilities = new WpUserMeta;
				$capabilities->meta_key = 'wp_' . $wpUser->blogId() . '_capabilities';
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
		$userConfig = $wpUser->meta()->where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $wpUser->blogId() . '_user_config')->first();
		if($userConfig) {
			$userConfig->meta_value = serialize($userConfigTemplate);
		} else {
			$userConfig = new WpUserMeta;
			$userConfig->meta_key = 'wp_' . $wpUser->blogId() . '_user_config';
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
					// deprecated
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
}
