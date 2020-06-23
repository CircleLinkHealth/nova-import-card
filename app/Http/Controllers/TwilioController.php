<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use CircleLinkHealth\Core\Jobs\ProcessTwilioSmsStatusCallbackJob;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    /**
     * Route called from Twilio whenever there is a status update on the SMS.
     *
     * @throws \Twilio\Exceptions\TwilioException
     * @return mixed
     */
    public function smsStatusCallback(\Illuminate\Http\Request $request)
    {
        $accountSid = $request->input('AccountSid');
        $messageSid = $request->input('MessageSid');
        if (empty($accountSid) || empty($messageSid)) {
            Log::warning("smsStatusCallback has missing params: accountSid[$accountSid] | messageSid[$messageSid]");
        } else {
            ProcessTwilioSmsStatusCallbackJob::dispatch($request->all());
        }

        return $this->responseWithXmlType(response(''));
    }

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
