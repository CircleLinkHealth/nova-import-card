<?php namespace App\Http\Controllers;

use App\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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

            \JWTAuth::setIdentifier('id');
            $user = \JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        } else {
            $input = $request->input();
            $newComment = new Comment();
            $newComment->user_id = $user->id;
            $newComment->comment_author = $input['comment_author'];
            $newComment->comment_author_email = 'admin@circlelinkhealth.com';
            $newComment->comment_author_url = 'https://www.circlelinkhealth.com/';
            $newComment->comment_author_IP = '127.0.0.1';

            //**Needs to be looked at - Possibly take Time Zone from the app
            $newComment->comment_date = Carbon::now();
            $newComment->comment_date_gmt = Carbon::now()->setTimezone('GMT');
            //**

            $newComment->comment_content = $input['comment_content'];
            $newComment->comment_karma = '0';
            $newComment->comment_approved = 1;
            $newComment->comment_agent = 'N/A';
            $newComment->comment_parent = $input['comment_parent'];
            $newComment->comment_type = $input['comment_type'];

            //Get Blog id for current user

            $blogTable = 'wp_' . $user->getBlogId($user->id) . '_comments';
            //$comm = new Comment();
            $newComment->setTable($blogTable);
            $saved = $newComment->save();




            if ($saved) {
                $response = [
                    'message' => 'Comment Stored!'
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
