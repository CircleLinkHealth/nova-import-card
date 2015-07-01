<?php namespace App\Http\Controllers;

use App\Comment;
use App\Observation;
use App\ObservationMeta;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ObservationController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
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
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
        $statusCode = 200;

        \JWTAuth::setIdentifier('ID');
        $user = \JWTAuth::parseToken()->authenticate();
        if(!$user) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        } else {

//************* COMMENT STORE BLOCK *************

            $input = $request->input();
            $newComment = new Comment();
            $newComment->user_id = $user->ID;
            $newComment->comment_author = $input['obs_message_id'];
            $newComment->comment_author_email = 'admin@circlelinkhealth.com';
            $newComment->comment_author_url = 'http://www.circlelinkhealth.com/';
            $newComment->comment_author_IP = '127.0.0.1';

            //**Needs to be looked at - Possibly take Time Zone from the app
            $newComment->comment_date = $input['obs_date'];
            $newComment->comment_date_gmt = Carbon::createFromFormat('Y-m-d H:i:s', $newComment->comment_date)->setTimezone('GMT');
            //**
            $commentContent = serialize(array($input['obs_message_id'] => $input['obs_value']));
            $newComment->comment_content = $commentContent;
            $newComment->comment_karma = '0';
            $newComment->comment_approved = 1;
            $newComment->comment_agent = 'N/A';
            $newComment->comment_parent = 1;
            $newComment->comment_type = 'manual_input';

            //Get Blog id for current user
            $blogTable = 'wp_'.$user->getBlogId($user->ID).'_comments';
            $newComment->setTable($blogTable);
            $newComment->save();

//************* OBSERVATION STORE BLOCK *************

            $newObservation = new Observation();
            $newObservation->comment_id = $newComment->comment_id;
            $newObservation->user_id = $user->ID;
            //Needs discussion
            $newObservation->obs_date = $input['obs_date'];
            $newObservation->obs_date_gmt = Carbon::createFromFormat('Y-m-d H:i:s', $newObservation->obs_date)->setTimezone('GMT');
            $newObservation->sequence_id = 0;
            $newObservation->obs_message_id = $input['obs_message_id'];
            $newObservation->obs_method = $newComment->comment_type;
            $newObservation->obs_key = $input['obs_key'];
            $newObservation->obs_value = $input['obs_value'];
            $newObservation->obs_unit = '';
            //$savedObs = $newObservation->save();

            //Get Blog id for current user
            $commentBlogTable = 'wp_'.$user->getBlogId($user->ID).'_comments';
            $obsBlogTable = 'ma_'.$user->getBlogId($user->ID).'_observations';
            //Set tables names
            $newComment->setTable($commentBlogTable);
            $newObservation->setTable($obsBlogTable);
            $savedObs = $newObservation->save();
            //Check if both queries were successful
            if($savedObs) {
                $response = [
                    'message' => 'Comment And Observation Stored!'
                ];
                return response()->json($response, $statusCode);
            } else {
                return response('Error', 500);
            }
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
