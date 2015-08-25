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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ObservationController extends Controller {

	/**
	 * @SWG\Get(
	 *     path="/observation",
	 *     summary="finds observations in the system",
	 *     tags={"Observation"},
	 *     @SWG\Response(
	 *         response=200,
	 *         description="this response",
	 *         @SWG\Schema(
	 *             type="array",
	 *             @SWG\Items(ref="#/definitions/Pet")
	 *         ),
	 *         @SWG\Header(header="x-expires", type="string")
	 *     ),
	 *     @SWG\Response(
	 *         response="default",
	 *         description="unexpected error",
	 *         @SWG\Schema(
	 *             ref="#/definitions/Error"
	 *         )
	 *     )
	 * )
	 */
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

    /**
     * Store a newly created resource in storage.
     * @SWG\Post(
	 * path="/observation",
	 * description="Appends comment to daily state_app and creates a new observation',
	 * produces={"application/json"},
     * @SWG\Parameter(
	 *         name="observation",
	 *         in="body",
	 *         description="Observation to the pet store",
	 *         required=true,
	 *         @SWG\Schema(ref="/observation"),
	 *     ),
     * @SWG\Response(
	 *         response=201,
	 *         description="pet response",
	 *         @SWG\Schema(ref="#/definitions/pet")
	 *     ),
	 * @SWG\Response(
	 *         response=500,
	 *         description="Error",
	 *         @SWG\Schema(ref="#/definitions/pet")
	 *     )
	 * )
	 * @SWG\Definition(
	 *     definition="observationStore",
	 *     allOf={
	 *         @SWG\Schema(ref="observation"),
	 *         @SWG\Schema(
	 *             required={"name"},
	 *             @SWG\Property(
	 *                 property="id",
	 *                 type="integer",
	 *                 format="int64"
	 *             )
	 *         )
	 *     }
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
			$wpUser = WpUser::find($input['userId']);
			if (!$wpUser) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
			// extra work here to decode from quirky UI
			$params['user_id'] = $wpUser->ID;
			$params['parent_id'] = 0; // needs to become state_app or state_sol??
			$params['obs_value'] = str_replace("/", "_", $input['observationValue']);
			$params['obs_date'] = $input['observationDate'];
			$params['obs_message_id'] = $input['observationType'];
			$params['obs_key'] = ''; // need to get from obs_message_id
			$params['timezone'] = 'America/New_York';
			$params['qstype'] = '';
			$params['source'] = $input['observationSource'];
		}

		$result = $observationService->storeObservationFromApp($params['user_id'], $params['parent_id'], $params['obs_value'], $params['obs_date'], $params['obs_message_id'], $params['obs_key'], $params['timezone'], $params['source']);

		if($result) {
			return response()->json(['response' => 'Observation Created'], 201);
		} else {
			return response()->json(['response' => 'Error'], 500);
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
