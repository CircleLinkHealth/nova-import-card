<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Request;

class TwilioController extends Controller
{
    /**
     * NOTE: make sure to white list this route in {@link \App\Http\Middleware\VerifyCsrfToken} middleware.
     */
    public function smsStatusCallback(Request $request)
    {
        //https://www.twilio.com/docs/sms/tutorials/how-to-confirm-delivery-php?code-sample=code-send-an-sms-with-a-statuscallback-url-1&code-language=PHP&code-sdk-version=5.x
        $sid    = $request->input('MessageSid');
        $status = $request->input('MessageStatus');
        //todo: save in db
    }
}
