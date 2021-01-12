<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Http\Controllers;

use CircleLinkHealth\TwilioIntegration\Http\Requests\TwilioInboundSmsRequest;
use CircleLinkHealth\TwilioIntegration\Jobs\ProcessCpmTwilioSmsStatusCallbackJob;
use CircleLinkHealth\TwilioIntegration\Jobs\ProcessTwilioInboundSmsJob;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    /**
     * Route called from Twilio whenever we receive an SMS.
     */
    public function smsInbound(Request $request)
    {
        ProcessTwilioInboundSmsJob::dispatch(new TwilioInboundSmsRequest($request->all()));

        return $this->responseWithXmlType(response(''));
    }

    /**
     * Route called from Twilio whenever there is a status update on the SMS.
     *
     * @throws \Twilio\Exceptions\TwilioException
     *
     * @return mixed
     */
    public function smsStatusCallback(Request $request)
    {
        $accountSid = $request->input('AccountSid');
        $messageSid = $request->input('MessageSid');
        if (empty($accountSid) || empty($messageSid)) {
            Log::warning("smsStatusCallback has missing params: accountSid[$accountSid] | messageSid[$messageSid]");
        } else {
            ProcessCpmTwilioSmsStatusCallbackJob::dispatch($request->all());
        }

        return $this->responseWithXmlType(response(''));
    }

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
