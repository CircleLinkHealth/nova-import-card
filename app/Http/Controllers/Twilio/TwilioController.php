<?php

namespace App\Http\Controllers\Twilio;

use App\Enrollee;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Twilio\Jwt\ClientToken;
use Twilio\Twiml;
use Twilio\Rest\Client;

class TwilioController extends Controller
{

    private $capability;
    private $token;

    public function __construct()
    {
        $this->capability = new ClientToken(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability->allowClientOutgoing(config('services.twilio.twiml-app-sid'));
        $this->token = $this->capability->generateToken();
    }

    public function obtainToken()
    {
        return response()->json(['token' => $this->token]);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Twilio\Exceptions\TwimlException
     * @throws \Exception
     */
    public function placeCall(Request $request)
    {
        $input      = $request->all();
        $validation = \Validator::make($input, [
            'From'             => '',
            'To'               => 'required',
            'InboundUserId'    => 'required',
            'OutboundUserId'   => 'required',
            'IsUnlistedNumber' => '',
        ]);

        if ($validation->fails()) {
            throw new \Exception("Invalid request");
        }

        $response = new Twiml();

        if ($request->has('From')) {
            //could be the practice outgoing phone number (in case of enrollment)
            //should we validate this number? or just let it fail if not accepted by Twilio?
            $callerIdNumber = $request->input('From');
        } else {
            $callerIdNumber = config('services.twilio')['from'];
        }

        $dial = $response->dial(['callerId' => $callerIdNumber]);

        $phoneNumberToDial = $input['To'];

        if ($phoneNumberToDial) {
            $dial->number($phoneNumberToDial);
        }

        return $this->responseWithXmlType(response($response));
    }

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }

    public function sendTestSMS()
    {

        $client = new Client($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);

        $smsQueue = Enrollee::toSMS()->get();

        foreach ($smsQueue as $recipient) {
            $provider_name = User::find($recipient->provider_id)->getFullName();

            if ($recipient->invite_sent_at == null) {
                //first go, make invite code:

                $recipient->invite_code     = rand(183, 982) . substr(uniqid(), -3);
                $link                       = url("join/$recipient->invite_code");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 1;
                $recipient->save();

                $message = "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link";

                $client->account->messages->create(

                // the number we are sending to - Any phone number
                    $recipient->cell_phone,
                    [

                        'from' => $recipient->practice->outgoing_phone_number,
                        'body' => $message,
                    ]
                );
            } else {
                $sad_face_emoji = "\u{1F648}";

                $link                       = url("join/$recipient->invite_code");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 2;
                $recipient->save();

                $message = "Dr. $provider_name hasn’t heard from you regarding their new wellness program. $sad_face_emoji Please enroll here: $link";

                $client->account->messages->create(

                // the number we are sending to - Any phone number
                    $recipient->cell_phone,
                    [

                        'from' => $recipient->practice->outgoing_phone_number,
                        'body' => $message,
                    ]
                );
            }
        }
    }

    /**
     * This function is called from Twilio (status callback)
     * - It inserts a record in our DB for raw logs (for debugging)
     * - It inspects the status request from Twilio and creates or updates any existing calls (using call sid)
     *
     * @param Request $request
     */
    public function callStatusCallback(Request $request)
    {
        try {
            $requestBody = $request->all();

            $callStatus = $requestBody['CallStatus'];
            TwilioRawLog::create([
                'call_status' => $callStatus,
                'log'         => json_encode($requestBody),
            ]);

            $callSid = $requestBody['CallSid'];

            TwilioCall::updateOrCreate(
                ['call_sid' => $callSid],
                [
                    'call_sid' => $callSid,
                    'call_status' => $callStatus,
                    'from' => '',
                    'to' => '',
                    'inbound_user_id' => '',
                    'outbound_user_id' => ''
                ]
            );

        } catch (\Throwable $e) {
            \Log::critical("Exception while storing twilio raw log: " . $e->getMessage());
        }
    }
}
