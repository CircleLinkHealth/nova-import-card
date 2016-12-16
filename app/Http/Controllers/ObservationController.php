<?php namespace App\Http\Controllers;

use App\Services\MsgCPRules;
use App\Services\ObservationService;
use App\User;
use Date;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Validator;

class ObservationController extends Controller {

	public function index(Request $request)
	{
		if ( $request->header('Client') == 'ui' )
		{
			$obs_id = Crypt::decrypt($request->header('obsId'));

			$wpUsers = (new User())->getObservation($obs_id);

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
 *         description="Observation Message id",
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
		$msgCPRules = new MsgCPRules;
		if ( $request->header('Client') == 'mobi' ) {
			// get and validate current user
            \JWTAuth::setIdentifier('id');
			$wpUser = \JWTAuth::parseToken()->authenticate();
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
			$params = $request->input();
            $params['user_id'] = $wpUser->id;
			$params['source'] = 'manual_input';
			$params['isStartingObs'] = 'N';
		} else if ( $request->header('Client') == 'ui' ) { // WP Site
			$input = json_decode(Crypt::decrypt($request->input('data')), true);
		} else {
			$input = $request->all();
		}

		if ( (!$request->header('Client')) || $request->header('Client') == 'ui' ) {
			$wpUser = User::find($input['userId']);
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

			//***** start extra work here to decode from quirky UI ******
			// creates params array to mimick the way mobi sends it

            $params['user_id'] = $wpUser->id;
			//$date = DateTime::createFromFormat("Y-m-d\TH:i", $input['observationDate']);
			$date = DateTime::createFromFormat("Y-m-d H:i", $input['observationDate']);
			$date = $date->format("Y-m-d H:i:s");

			if(isset($input['parent_id'])) {
				$params['parent_id'] = $input['parent_id'];
			}
			$params['parent_id'] = 0;
			$params['obs_value'] = str_replace("/", "_", $input['observationValue']);
			$params['obs_date'] = $date;
			$params['obs_message_id'] = $input['observationType'];
			$params['obs_key'] = ''; // need to get from obs_message_id
			$params['timezone'] = 'America/New_York';
			$params['qstype'] = '';
			$params['source'] = $input['observationSource'];
			$params['isStartingObs'] = 'N';
			if(isset($input['isStartingObs'])) {
				$params['isStartingObs'] = $input['isStartingObs'];
			}

			//***** end extra work here to decode from quirky UI ******
		}

		// process message id (ui dropdown includes qstype)
		$pieces = explode('/', $params['obs_message_id']);
		if (count($pieces) == 1) {
			// normal message, straight /messageId
			$obsMessageId = $params['obs_message_id'];
		} else if (count($pieces) == 2) {
			// semi-normal message, qstype/messageId
			$qstype = $pieces[0];
			$obsMessageId = $pieces[1];
		}

		// validate answer
		$qsType = $msgCPRules->getQsType($obsMessageId, '16');
		$answerResponse =  $msgCPRules->getValidAnswer('16', $qsType, $obsMessageId, $params['obs_value'], false);
		if(!$answerResponse) {
			return redirect()->back()->withErrors(['You entered an invalid value, please review and resubmit.'])->withInput();
		}

		// validate timezone
		/*
		if(strlen($params['timezone']) < 5) {
			return response()->json(['response' => 'Error - Invalid timezone, please provide full timezone (not GMT offset)'], 500);
		}
		*/

		$result = $observationService->storeObservationFromApp($params['user_id'], $params['parent_id'], $params['obs_value'], $params['obs_date'], $obsMessageId, $params['obs_key'], $params['timezone'], $params['source'], $params['isStartingObs']);


		if ( $request->header('Client') == 'mobi' || $request->header('Client') == 'ui' ) {
			// api response
			if ($result) {
				return response()->json(['response' => 'Observation Created'], 201);
			} else {
				return response()->json(['response' => 'Error'], 500);
			}
		} else {
			// ui response
            return redirect()->route('patient.summary', [
                'id'        => $wpUser->id,
                'programId' => $input['programId'],
            ])->with('messages', ['successfully added new observation'])->send();
		}
    }


	/**
	 * @SWG\Get(
	 *     path="/observations/{id}",
     *     description="Returns a observations based on a single id",
	 *     operationId="observations",
	 *     @SWG\Parameter(
     *         description="id of observations to fetch",
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
