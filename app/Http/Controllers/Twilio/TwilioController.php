<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Twilio;

use App\Contracts\Services\TwilioClientable;
use App\Http\Controllers\Controller;
use App\Jobs\TwilioNotificationStatusUpdateJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class TwilioController extends Controller
{
    private $client;

    private $service;

    public function __construct(TwilioClientable $twilioClientService)
    {
        $this->service = $twilioClientService;
        $this->client  = $twilioClientService->getClient();
    }

    public function smsStatusCallback(Request $request)
    {
        $accountSid = $request->input('AccountSid');
        $messageSid = $request->input('MessageSid');
        if (empty($accountSid) || empty($messageSid)) {
            Log::warning("smsStatusCallback has missing params: accountSid[$accountSid] | messageSid[$messageSid]");
        } else {
            TwilioNotificationStatusUpdateJob::dispatch(
                $messageSid,
                $accountSid,
                [
                    'value'   => $request->input('MessageStatus'),
                    'details' => 'todo',
                ],
            );
        }

        return $this->responseWithXmlType(response(''));
    }

    private function responseWithXmlData(
        array $vars,
        $status = 200,
        array $header = [],
        $rootElement = 'response',
        $xml = null
    ) {
        if (is_null($xml)) {
            $xml = new SimpleXMLElement('<'.$rootElement.'/>');
        }

        foreach ($vars as $key => $value) {
            if (is_array($value)) {
                $this->responseWithXmlData($value, $status, $header, $rootElement, $xml->addChild($key));
            } else {
                if (preg_match('/^@.+/', $key)) {
                    $attributeName = preg_replace('/^@/', '', $key);
                    $xml->addAttribute($attributeName, $value);
                } else {
                    $xml->addChild($key, $value);
                }
            }
        }

        if (empty($header)) {
            $header['Content-Type'] = 'application/xml';
        }

        return \Response::make($xml->asXML(), $status, $header);
    }

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
