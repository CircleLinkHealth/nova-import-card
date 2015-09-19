<?php namespace App\Http\Controllers;

use App\Comment;
use App\Observation;
use App\ObservationMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\MsgChooser;
use App\Services\MsgCPRules;
use App\Services\ObservationService;
use App\WpUser;
use Date;
use DateTime;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller {

	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' )
		{
			$obs_id = Crypt::decrypt($request->header('obsId'));

			$wpUsers = (new WpUser())->getObservation($obs_id);

			return response()->json( Crypt::encrypt( json_encode( $wpUsers ) ) );
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


/** @SWG\Post(
*     path="/observation",
*     tags={"observation"},
*     operationId="createObservation",
 *     summary="Appends comment to daily state_app and creates a new observation",
 *     description="",
 *     consumes={"application/json", "application/xml"},
 *     produces={"application/xml", "application/json"},
 * @SWG\Parameter(
 *         name="Authorization",
 *         type="string",
 *         in="header",
 *         description="Token",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 * @SWG\Parameter(
 *         name="X-Authorization",
 *         in="header",
 *         type="string",
 *         description="API Key",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 * @SWG\Parameter(
 *         name="client",
 *         in="header",
 *         type="string",
 *         description="mobi/ui",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="parent_id",
 *         in="body",
 *         description="Id of the state_app record for the given day",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_message_id",
 *         in="body",
 *         description="Observation Message ID",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_key",
 *         in="body",
 *         description="Observation Key",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *      @SWG\Parameter(
 *         name="obs_value",
 *         in="body",
 *         description="Observation Value",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="obs_date",
 *         in="body",
 *         description="Created timestamp",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Parameter(
 *         name="timezone",
 *         in="body",
 *         description="User's timezone",
 *         required=true,
 *         @SWG\Schema(ref="#/definitions/Observation"),
 *     ),
 *     @SWG\Response(
 *         response=201,
 *         description="Success",
 *     ),
 *     @SWG\Response(
 *         response=500,
 *         description="Error",
 *     ),
 *     security={{"petstore_auth":{"write:pets", "read:pets"}}}
 * )
 */

    public function store(Request $request)
    {
		$observationService = new ObservationService;
		if ( $request->header('Client') == 'mobi' ) {
			// get and validate current user
			\JWTAuth::setIdentifier('ID');
			$wpUser = \JWTAuth::parseToken()->authenticate();
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
			$params = $request->input();
			$params['user_id'] = $wpUser->ID;
			$params['source'] = 'manual_input';
		} else if ( $request->header('Client') == 'ui' ) { // WP Site
			$input = json_decode(Crypt::decrypt($request->input('data')), true);
		} else {
			$input = $request->all();
		}

		if ( (!$request->header('Client')) || $request->header('Client') == 'ui' ) {
			$wpUser = WpUser::find($input['userId']);
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
			$validator = Validator::make($input, [
				'observationDate' => 'required|date',
				'observationValue' => 'required',
				'observationSource' => 'required'
			]);
			if ($validator->fails()) {
				if ( $request->header('Client') ) {
					return response()->json(['response' => 'Validation Error'], 500);
				} else {
					return redirect()->back()->withErrors($validator)->withInput();
				}
			}

			// extra work here to decode from quirky UI
			$params['user_id'] = $wpUser->ID;
			// get state_app for obs_date
			$date = DateTime::createFromFormat("Y-m-d\TH:i", $input['observationDate']);
			$comment = Comment::where('comment_type', '=', 'state_app')
				->where('user_id', '=', $wpUser->ID)
				->where('comment_date', '>=', $date->format("Y-m-d") . ' 00:00:00')
				->where('comment_date', '<=', $date->format("Y-m-d") . ' 23:59:59')
				->orderBy('id', 'desc')
				->first();
			if (!$comment) {
				return response()->json(['response' => 'No state_app comment found'], 500);
			}
			$params['parent_id'] = $comment->id;
			$params['obs_value'] = str_replace("/", "_", $input['observationValue']);
			$params['obs_date'] = $input['observationDate'];
			$params['obs_message_id'] = $input['observationType'];
			$params['obs_key'] = ''; // need to get from obs_message_id
			$params['timezone'] = 'America/New_York';
			$params['qstype'] = '';
			$params['source'] = $input['observationSource'];
		}

		$result = $observationService->storeObservationFromApp($params['user_id'], $params['parent_id'], $params['obs_value'], $params['obs_date'], $params['obs_message_id'], $params['obs_key'], $params['timezone'], $params['source']);

		if ( $request->header('Client') == 'mobi' || $request->header('Client') == 'ui' ) {
			// api response
			if ($result) {
				return response()->json(['response' => 'Observation Created'], 201);
			} else {
				return response()->json(['response' => 'Error'], 500);
			}
		} else {
			// ui response
			return redirect()->route('patient.summary', ['id' => $wpUser->ID, 'programId' => $input['programId']])->with('messages', ['successfully added new observation'])->send();
		}
    }


	/**
	 * @SWG\Get(
	 *     path="/observations/{id}",
	 *     description="Returns a observations based on a single ID",
	 *     operationId="observations",
	 *     @SWG\Parameter(
	 *         description="ID of observations to fetch",
	 *         format="int64",
	 *         in="path",
	 *         name="id",
	 *         required=true,
	 *         type="integer"
	 *     ),
	 *     produces={
	 *         "application/json",
	 *         "application/xml",
	 *         "text/html",
	 *         "text/xml"
	 *     },
	 *     @SWG\Response(
	 *         response=200,
	 *         description="pet response",
	 *         @SWG\Schema(ref="#/definitions/observations")
	 *     ),
	 *     @SWG\Response(
	 *         response="default",
	 *         description="unexpected error",
	 *         @SWG\Schema(ref="#/definitions/errorModel")
	 *     )
	 * )
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
