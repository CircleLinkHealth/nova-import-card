<?php namespace App\Http\Controllers\CCDAPI;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ApiController extends Controller {

	/*
	 * Route::post('/ehr-api/login', function(){

	$credentials = \Input::only('email', 'user_pass');
	\JWTAuth::setIdentifier('ID');
	//$user = \JWTAuth::parseToken()->authenticate();
	if ( ! $token = \JWTAuth::attempt($credentials) )
	{
		return response()->json(['error' => 'invalid_credentials'], 400);
	}
	return response()->json(compact('token'));
});

Route::post('/ehr-api/upload', function(Request $request){
	$user = \JWTAuth::parseToken()->authenticate();

	if($user->hasRole('ccd-vendor')) {
		$ccd = base64_decode($request['ccd_base64']);
		//Save to CCD object or table
		return response()->json(['message' => $ccd]);
	}
	return response()->json($user);
});
	 */
	public function login()
	{
		$credentials = \Input::only('email', 'user_pass');
		\JWTAuth::setIdentifier('ID');
		//$user = \JWTAuth::parseToken()->authenticate();
		if ( ! $token = \JWTAuth::attempt($credentials) )
		{
			return response()->json(['error' => 'invalid_credentials'], 400);
		}
		return response()->json(compact('token'));
	}

	public function create()
	{
		$user = \JWTAuth::parseToken()->authenticate();

		if($user->hasRole('ccd-vendor')) {
			$ccd = base64_decode($request['ccd_base64']);
			//Save to CCD object or table
			return response()->json(['message' => $ccd]);
		}
		return response()->json($user);
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

}
