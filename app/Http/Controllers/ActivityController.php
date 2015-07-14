<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\WpUser;
use App\WpUserMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/**
 * Class ActivityController
 * @package App\Http\Controllers
 */
class ActivityController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' ) {
			// 'ui' api request
			$user_id = Crypt::decrypt($request->header('UserId'));
			$activities = (new Activity())->getActivitiesWithMeta($user_id);
			return response()->json( Crypt::encrypt( json_encode( $activities ) ) );
		} else {
			// display view
			$activities = Activity::orderBy('id', 'desc')->get();
			return view('activities.index', [ 'activities' => $activities ]);
		}
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
	 * @param  array  $params
	 * @return Response
	 */
	public function store($params = false, Request $request)
	{
		if($params) {
			$input = $params;
		} else if ( $request->isJson() ) {
			$input = $request->input();
		} else if ( $request->isMethod('POST') ) {
			if ( $request->header('Client') == 'ui' ) { // WP Site
				$input = json_decode(Crypt::decrypt($request->input('data')), true);
			}
		} else {
			return response("Unauthorized", 401);
		}

		if (array_key_exists('meta',$input)) {
			$meta = $input['meta'];
			unset($input['meta']);
		} else {
			return response("Bad request", 400);
		}

		$actId = Activity::createNewActivity($input);

		// add meta
		$activity = Activity::find($actId);
		$metaArray = [];
		$i = 0;
		foreach ($meta as $actMeta) {
			$metaArray[$i] = new ActivityMeta($actMeta);
			$i++;
		}
		$activity->meta()->saveMany($metaArray);

		// update usermeta: cur_month_activity_time
		$userMeta = WpUserMeta::where('user_id', '=', $input['patient_id'])
			->where('meta_key', '=', 'cur_month_activity_time')->first();

		if(!$userMeta) {
			// add in initial user meta: cur_month_activity_time
			$newUserMetaAttr = array(
				'user_id' => $input['patient_id'],
				'meta_key' => 'cur_month_activity_time',
				'meta_value' => $input['duration'],
			);
			$newUserMeta = WpUserMeta::create($newUserMetaAttr);
			//echo "<pre>CREATED";var_dump($newUserMeta);echo "</pre>";die();
		} else {
				// update existing user meta: cur_month_activity_time
				$activityTotal = ($input['duration'] + $userMeta->meta_value);
				$userMeta = WpUserMeta::where('user_id', '=', $input['patient_id'])
					->where('meta_key', '=', 'cur_month_activity_time')
					->update(array('meta_value' => $activityTotal));
				//echo "<pre>UPDATED";var_dump($activityTotal);echo "</pre>";die();
		}

		return response("Activity Created", 201);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id, Request $request)
	{
        if ( $request->isMethod('GET') )
        {
            if ( $request->header('Client') == 'ui' ) // WP Site
            {
                $activity = Activity::findOrFail($id);

                //extract and attach the 'comment' value from the ActivityMeta table
                $metaComment = $activity->getActivityCommentFromMeta($id);
                $activity['comment'] = $metaComment;
                $activity['message'] = 'OK';
                $json = Array();
                $json['body'] = $activity;
                $json['message'] = 'OK';
                return response(Crypt::encrypt(json_encode($json)));
            }
        }
        else
        {
            return response("Unauthorized", 401);
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
     * @param Request $request
     * @return Response
     */
	public function update(Request $request)
	{
        if ( $request->isJson() )
        {
            $input = $request->input();
        }
        else if ( $request->isMethod('POST') )
        {
            if ( $request->header('Client') == 'ui' ) // WP Site
            {
                $input = json_decode(Crypt::decrypt($request->input('data')), true);
            }
        }
        else
        {
            return response("Unauthorized", 401);
        }

       //  Check if there are any meta nested parts in the incoming request
        $meta = $input['meta'];
        unset($input['meta']);

        $activity = Activity::find($input['activity_id']);
        $activity->fill($input)->save();

        $actMeta = ActivityMeta::where('activity_id', $input['activity_id'])->where('meta_key',$meta['0']['meta_key'])->first();
        $actMeta->fill($meta['0'])->save();

        return response("Activity Updated", 201);
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
