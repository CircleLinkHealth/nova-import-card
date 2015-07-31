<?php namespace App\Http\Controllers\Redox;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use App\ThirdPartyApiConfig;

class AppVerificationController extends Controller {

//curl -X POST https://www.redoxengine.com/api/auth/authenticate -d '{"apiKey": "863a03c8-d47c-4187-9073-88986091714f", "secret": "michalisantoniou"}' -H "Content-Type: application/json"
//curl -X POST https://www.redoxengine.com/api/auth/refreshToken -d '{"apiKey": "863a03c8-d47c-4187-9073-88986091714f", "refreshToken": "16a0ecdf-fe7d-4707-8515-9f09830b7c67"}' -H "Content-Type: application/json"
    /**
     * This is the endpoint Redox will use to verify the app that we created.
     * Redox will send a verification-token and challenge as query string.
     * This function checks if the verification-token mathces and returns the challenge.
     *
     * @param Request $request
     * @return string
     */
	public function getVerificationRequest(Request $request)
    {
        $challenge = $request->input('challenge');
        $verificationToken = $request->input('verification-token');

        $getAppVerifToken = ThirdPartyApiConfig::select('meta_value')->whereMetaKey('redox_app_verification_token')->first()->toArray();

        if ( !empty( $getAppVerifToken ) ) {
            $appVerifToken = $getAppVerifToken['meta_value'];

            if ($verificationToken == $appVerifToken) {
                return $challenge;
            }
        }

        return response('Error verifying your Redox app', 401);
    }

    /*
     * Still working here
     */
    public function postRedox(Request $request)
    {
        return response('OK', 200);
    }

}
