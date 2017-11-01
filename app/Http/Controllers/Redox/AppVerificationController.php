<?php namespace App\Http\Controllers\Redox;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use App\ThirdPartyApiConfig;

class AppVerificationController extends Controller
{

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

        $getAppVerifToken = ThirdPartyApiConfig::select('meta_value')
            ->whereMetaKey('redox_app_verification_token')
            ->first();

        if (!empty($getAppVerifToken)) {
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
