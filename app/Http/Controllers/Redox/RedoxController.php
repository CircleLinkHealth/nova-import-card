<?php namespace App\Http\Controllers\Redox;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RedoxController extends Controller {

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

        if ( $verificationToken == 'iamtestinghere' )
        {
            return $challenge;
        }

//        return response('OK', 200);
    }

    public function postRedox(Request $request)
    {
        return response('OK', 200);
    }

}
