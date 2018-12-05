<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorizationController extends Controller
{
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
        $credentials = $request->only('email', 'password');

        // set the identifier for wp_users
        \JWTAuth::setIdentifier('id');

        // attempt authorization
        if ( ! $token = \JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 400);
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
        \JWTAuth::setIdentifier('id');
        $token = \JWTAuth::getToken();
        $user  = \JWTAuth::parseToken()->authenticate();

        return response()->json($user);
    }
}
