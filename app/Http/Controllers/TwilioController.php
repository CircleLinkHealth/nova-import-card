<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Enrollee;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Client;
use Twilio\Twiml;

class TwilioController extends Controller
{
    private $capability;
    private $token;

    public function __construct(Request $request)
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
     * @throws \Twilio\Exceptions\TwimlException
     * @throws \Exception
     *
     * @return mixed
     */
    public function placeCall(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'To'   => 'required|phone:AUTO,US',
            'From' => 'nullable|phone:AUTO,US', //could be the practice outgoing phone number (in case of enrollment)
        ]);

        if ($validation->fails()) {
            //twilio will just respond with 'An application error has occurred'
            throw new \Exception('Invalid phone number');
        }

        $response = new Twiml();

        if ($request->has('From')) {
            $callerIdNumber = $request->input('From');
        } else {
            $callerIdNumber = config('services.twilio')['from'];
        }

        $dial = $response->dial(['callerId' => $callerIdNumber]);

        $phoneNumberToDial = $request->input('To');

        if ($phoneNumberToDial) {
            $dial->number($phoneNumberToDial);
        }

        return $this->responseWithXmlType(response($response));
    }

    public function sendTestSMS()
    {
        $client = new Client($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);

        $smsQueue = Enrollee::toSMS()->get();

        foreach ($smsQueue as $recipient) {
            $provider_name = User::find($recipient->provider_id)->getFullName();

            if (null == $recipient->invite_sent_at) {
                //first go, make invite code:

                $recipient->invite_code     = rand(183, 982).substr(uniqid(), -3);
                $link                       = url("join/{$recipient->invite_code}");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 1;
                $recipient->save();

                $message = "Dr. ${provider_name} has invited you to their new wellness program! Please enroll here: ${link}";

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

                $link                       = url("join/{$recipient->invite_code}");
                $recipient->invite_sent_at  = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count   = 2;
                $recipient->save();

                $message = "Dr. ${provider_name} hasn’t heard from you regarding their new wellness program. ${sad_face_emoji} Please enroll here: ${link}";

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

    private function responseWithXmlType($response)
    {
        return $response->header('Content-Type', 'application/xml');
    }
}
