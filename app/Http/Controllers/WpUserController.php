<?php namespace App\Http\Controllers;

use App\Activity;
use App\WpBlog;
use App\WpUser;
use App\WpUserMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTimeZone;

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
		//
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
					$result = (new Activity())->reprocessMonthlyActivityTime($id);
					if ($result) {
						$messageKey = 'success';
						$messageValue = 'User activities have been recalculated';
					}
				}
			}
		}
		$wpUser = WpUser::find($id);
		$activiyTotal = (new Activity())->getTotalActivityTimeForMonth($id);
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
