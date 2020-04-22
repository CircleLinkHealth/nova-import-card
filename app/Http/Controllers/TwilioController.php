<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 01/07/2019
 * Time: 6:06 PM.
 */

namespace App\Http\Controllers;

use Request;

class TwilioController extends Controller
{
    /**
     * NOTE: make sure to white list this route in {@link \App\Http\Middleware\VerifyCsrfToken} middleware.
     *
     * @param Request $request
     */
    public function smsStatusCallback(Request $request)
    {
        //https://www.twilio.com/docs/sms/tutorials/how-to-confirm-delivery-php?code-sample=code-send-an-sms-with-a-statuscallback-url-1&code-language=PHP&code-sdk-version=5.x
        $sid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');
        //todo: save in db
    }
}
