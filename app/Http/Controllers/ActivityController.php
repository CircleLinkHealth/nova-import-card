<?php namespace App\Http\Controllers;

use App\Activity;
use App\ActivityMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ActivityController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' )
		{
			$user_id = Crypt::decrypt($request->header('UserId'));

			$activities = (new Activity())->getActivitiesWithMeta($user_id);

			return response()->json( Crypt::encrypt( json_encode( $activities ) ) );
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
	 * @return Response
	 */
	public function store(Request $request)
	{
		if ( $request->isJson() )
		{
			$input = $request->input();
		}
		else if ( $request->isMethod('POST') ) //WP Site
		{
			$input = $request->input('data');
		}
		else
		{
			return response("Unauthorized", 401);
		}

		if (array_key_exists('meta',$input))
		{
			$meta = $input['meta'];
			unset($input['meta']);
		}
		else
		{
			return response("Bad request", 400);
		}

		$actId = Activity::createNewActivity($input);

		$activity = Activity::find($actId);

		$metaArray = [];
		$i = 0;
		foreach ($meta as $actMeta)
		{
			$metaArray[$i] = new ActivityMeta($actMeta);
			$i++;
		}

		$activity->meta()->saveMany($metaArray);

		return response("Activity Created", 201);
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
