<?php namespace App\Http\Controllers\CcdApi\Aprima;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class AuthController extends Controller {

    /**
     * This function will authenticate te user using their username and password and return an access token.
     *
     * @param Request $request
     * @return string access_token
     */
    public function getAccessToken(Request $request)
    {
        if ( !$request->has( 'username' ) || !$request->has( 'password' ) ) {
            response()->json( ['error' => 'Username and password need to be included on the request.'], 400 );
        }

        $credentials = [
            'email' => $request->input( 'username' ),
            'user_pass' => $request->input( 'password' ),
        ];

        \JWTAuth::setIdentifier( 'ID' );

        if ( !$access_token = \JWTAuth::attempt( $credentials ) ) {
            return response()->json( ['error' => 'Invalid Credentials.'], 400 );
        }

        return response()->json( compact( 'access_token' ), 200 );
    }

}
