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
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        \JWTAuth::setIdentifier('ID');
        $user = \JWTAuth::parseToken()->authenticate();
        if(!$user) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        } else {

//************* COMMENT UPDATE BLOCK *************

            $input = $request->input();
            $comment = Comment::find($input['parent_id']);
            //$comment = new Comment();
            $comment_array = unserialize($comment['comment_content']);
            $comment_array[][$input['obs_key']] = $input['obs_value'];
            $comment->comment_content = serialize($comment_array);
            $comment->comment_type = 'state_app';
            $commentBlogTable = 'wp_'.$user->getBlogId($user->ID).'_comments';
            $comment->setTable($commentBlogTable);
            $savedComm = $comment->save();

            $newObservation = new Observation();
            $newObservation->comment_id = $comment->comment_ID;
            $newObservation->user_id = $user->ID;
            //Needs discussion
            $newObservation->obs_date = $input['obs_date'];
            $newObservation->obs_date_gmt = Carbon::createFromFormat('Y-m-d H:i:s', $newObservation->obs_date)->setTimezone('GMT');
            $newObservation->sequence_id = 0;
            $newObservation->obs_message_id = $input['obs_message_id'];
            $newObservation->obs_method = $comment->comment_type;
            $newObservation->obs_key = $input['obs_key'];
            $newObservation->obs_value = $input['obs_value'];
            $newObservation->obs_unit = '';
            //$savedObs = $newObservation->save();

            //Get Blog id for current user
            $obsBlogTable = 'ma_'.$user->getBlogId($user->ID).'_observations';
            //Set tables names
            $newObservation->setTable($obsBlogTable);
            $savedObs = $newObservation->save();
            if($savedComm&&$savedComm) {
                return response()->json($comment->comment_content, 201);
                ;
            } else {
                return response()->json('Error', 500);
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
