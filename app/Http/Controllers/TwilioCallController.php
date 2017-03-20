<?php

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Middleware\Debugbar;
use Illuminate\Http\Request;
use Twilio\Jwt\ClientToken;
use Twilio\Twiml;


class TwilioCallController extends Controller
{

    private $capability;
    private $token;

    public function __construct(Request $request)
    {

        $this->capability = new ClientToken($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);
        $this->capability->allowClientOutgoing($_ENV['TWILIO_ENROLLMENT_TWIML_APP_SID']);
        $this->capability->allowClientIncoming('jenny');
        $this->token = $this->capability->generateToken();

    }

    public function obtainToken()
    {

        return response()->json(['token' => $this->capability->generateToken()]);


    }

    public function makeCall()
    {

        return view('partials.calls.make-twilio-call',
            [
                'token' => $this->token,
            ]);

    }

    public function newCall(Request $request)
    {
        Debugbar::disable();

        $response = new Twiml();
        $callerIdNumber = $_ENV['TWILIO_FROM'];

        $dial = $response->dial(['callerId' => $callerIdNumber]);

        $phoneNumberToDial = $request->input('phoneNumber');

        $dial->number($phoneNumberToDial);

        return $response;
    }


}
