<?php namespace App\Http\Controllers;

use App\Activity;
use App\WpBlog;
use App\Location;
use App\WpUser;
use App\WpUserMeta;
use App\Services\ActivityService;
use App\Services\CareplanService;
use App\Services\MsgUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTimeZone;
use PasswordHash;
use Auth;

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
			$wpUsers = wpUser::orderBy('ID', 'desc')->get();
			return view('wpUsers.index', [ 'wpUsers' => $wpUsers ]);
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
		$rules = array(
			'name'             => 'required',                        // just a normal required validation
			'email'            => 'required|email|unique:ducks',     // required and must be unique in the ducks table
			'password'         => 'required',
			'password_confirm' => 'required|same:password'           // required and has to match the password field
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request, $id)
	{
		$messageKey = 'key';
		$messageValue = 'value';
		$params = $request->input();
		if(!empty($params)) {
			if(isset($params['action'])) {
				if($params['action'] == 'recalcActivities') {
					$activityService = new ActivityService;
					$result = $activityService->reprocessMonthlyActivityTime($id);
					if ($result) {
						$messageKey = 'success';
						$messageValue = 'User activities have been recalculated';
					}
				} else if($params['action'] == 'setPatientToBlog') {
					$userMeta = new WpUserMeta;
					$userMeta->meta_key = 'primary_blog';
					$userMeta->meta_value = $params['blogId'];
					$userMeta->user_id = $id;
					$userMeta->save ();
					//$messageKey = 'success';
					//$messageValue = 'Usermeta primary_blog set for user '.$id;
					return redirect()->back()->with('messages', ['successfully updated Usermeta primary_blog']);
				}
			}
		}
		$wpUser = WpUser::find($id);
		$activityService = new ActivityService;
		$activiyTotal = $activityService->getTotalActivityTimeForMonth($id);
		if($wpUser) {
			return view('wpUsers.show', ['wpUser' => $wpUser, 'activityTotal' => $activiyTotal,
				$messageKey => $messageValue]);
		} else {
			return response("User not found", 401);
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
		$messages = \Session::get('messages');

		$wpUser = WpUser::find($id);
		if(!$wpUser) {
			return response("User not found", 401);
		}

		// primary_blog
		$userMeta = WpUserMeta::where('user_id', '=', $id)->lists('meta_value', 'meta_key');
		if(!isset($userMeta['primary_blog'])) {
			return response("Required meta primary_blog not found", 401);
		}
		$primaryBlog = $userMeta['primary_blog'];

		// user config
		$userConfig = $wpUser->userConfigTemplate();
		if(isset($userMeta['wp_' . $primaryBlog . '_user_config'])) {
			$userConfig = unserialize($userMeta['wp_' . $primaryBlog . '_user_config']);
			$userConfig = array_merge($wpUser->userConfigTemplate(), $userConfig);
		}

		// set role
		$capabilities = unserialize($userMeta['wp_' . $primaryBlog . '_capabilities']);
		$role = key($capabilities);

		//dd($capabilities);

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
		return view('wpUsers.edit', ['wpUser' => $wpUser, 'locations_arr' => $locations_arr, 'states_arr' => $states_arr, 'timezones_arr' => $timezones_arr, 'wpBlogs' => $wpBlogs, 'userConfig' => $userConfig, 'userMeta' => $userMeta, 'primaryBlog' => $primaryBlog, 'role' => $role, 'providers_arr' => $providers_arr, 'messages' => $messages]);
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
		$wpUser = WpUser::find($id);

		// get user meta
		$userMeta = WpUserMeta::where('user_id', '=', $id)->lists('meta_value', 'meta_key');
		$primaryBlog = $userMeta['primary_blog'];

		// update role
		$input = $request->input('role');
		if(!empty($input)) {
			$capabilities = WpUserMeta::where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_capabilities')->first();
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
		$userConfig = WpUserMeta::where('user_id', '=', $id)->where('meta_key', '=', 'wp_' . $primaryBlog . '_user_config')->first();
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
		$params = $request->input();
		$wpUser = WpUser::find($id);
		if(!$wpUser) {
			return response("User not found", 401);
		}
		$userMeta = $wpUser->userMeta();
		if(!empty($params)) {
			if(isset($params['action'])) {
				if($params['action'] == 'sendTextSimulation') {
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
				}
			}
		}
		/*
		$arrPart = array($wpUser->ID => array());
		$arrPart[$wpUser->ID]['usermeta'] = $userMeta;
		$arrPart[$wpUser->ID]['usermeta']['curresp'] = 'SYM';
		$arrPart[$wpUser->ID]['usermeta']['intProgramId'] = $wpUser->blogId();
		$msgUser = new MsgUser;
		$userSmsState = $msgUser->userSmsState($arrPart);
		dd($userSmsState);
		*/
		//dd('dies early');

		$msgUsers = new MsgUser;
		$commentsForUser = $msgUsers->get_comments_for_user($wpUser->ID, $wpUser->blogId());
		//dd($commentsForUser);
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
		//dd($comments);

		return view('wpUsers.msgCenter', ['wpUser' => $wpUser, 'userMeta' => $userMeta, 'comments' => $comments, 'messages' => array()]);
	}
}
