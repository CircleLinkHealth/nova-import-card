<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class AuthorizationController extends Controller {

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
	public function store()
	{
		//
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

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function login(Request $request)
	{
		// testing
		//$user = User::first();
		//$token = JWTAuth::fromUser($user);
		//return Response::json(compact('token'));

		// testing
		//dd( $token = JWTAuth::attempt($credentials) );

		// working code
		// get credentials
		$credentials = \Input::only('user_email', 'user_pass');

		// set the identifier for wp_users
		\JWTAuth::setIdentifier('ID');

		// attempt authorization
		if ( ! $token = \JWTAuth::attempt($credentials) )
		{
			// return 401 error response
			return response()->json(['error' => 'invalid_credentials'], 401);
		}
		return response()->json(compact('token'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function tokentest(Request $request)
	{
		//return response()->json('test');
		$headers = $request->header();
		//return response()->json($headers);
		//return Response::json('testing');

		// set the identifier for wp_users
		\JWTAuth::setIdentifier('ID');
		$token = \JWTAuth::getToken();
		$user = \JWTAuth::parseToken()->authenticate();
		return response()->json($user);
	}

}
