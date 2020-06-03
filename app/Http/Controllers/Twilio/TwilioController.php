<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Twilio;

use App\Contracts\Services\TwilioClientable;
use App\Http\Controllers\Controller;
use App\Jobs\TwilioNotificationStatusUpdateJob;
use App\OutgoingSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class TwilioController extends Controller
{
    public const TWILIO_MESSAGE_DELIVERY_CODES = [
        '30001' => 'Queue overflow',
        '30002' => 'Account suspended',
        '30003' => 'Unreachable destination handset',
        '30004' => 'Message blocked',
        '30005' => 'Unknown destination handset',
        '30006' => 'Landline or unreachable carrier',
        '30007' => 'Carrier violation',
        '30008' => 'Unknown error',
    ];

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
            $status        = $request->input('MessageStatus');
            $statusDetails = $request->input('ErrorCode', null);
            if ($statusDetails && isset(self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails])) {
                $statusDetails = self::TWILIO_MESSAGE_DELIVERY_CODES[$statusDetails];
            }

            //check if this message was initiated from SuperAdmin -> SMS (OutgoingSms Model)
            //if not, assume it's a notification
            $handled = $this->handleOutgoingSmsCallback($messageSid, $accountSid, $status, $statusDetails);
            if ( ! $handled) {
                $this->handleSmsNotificationCallback($messageSid, $accountSid, $status, $statusDetails);
            }
        }

        return $this->responseWithXmlType(response(''));
    }

    private function handleOutgoingSmsCallback(string $sid, string $accountSid, string $status, string $statusDetails = null)
    {
        /** @var OutgoingSms $sms */
        $sms = OutgoingSms::where('sid', '=', $sid)
            ->where('account_sid', '=', $accountSid)
            ->first();
        if ($sms) {
            $sms->status         = $status;
            $sms->status_details = $statusDetails;
            $sms->save();

            return true;
        }

        return false;
    }

    private function handleSmsNotificationCallback(string $sid, string $accountSid, string $status, string $statusDetails = null)
    {
        TwilioNotificationStatusUpdateJob::dispatch(
            $sid,
            $accountSid,
            [
                'value'   => $status,
                'details' => $statusDetails,
            ],
        );
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
