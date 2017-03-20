<?php

namespace App\Http\Controllers;

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

        // put a phone number you've verified with Twilio to use as a caller ID number
        $callerId = "+14694496305";

        // put your default Twilio Client name here, for when a phone number isn't given
        $number = "CircleLink Health";

        // get the phone number from the page request parameters, if given
        if (isset($_REQUEST['PhoneNumber'])) {
            $number = htmlspecialchars($_REQUEST['PhoneNumber']);
        }

        // wrap the phone number or client name in the appropriate TwiML verb
        // by checking if the number given has only digits and format symbols
        $numberOrClient = "<Number>" . $number . "</Number>";

        return view('partials.calls.make-twilio-call',
            [
                'token' => $this->token,
                'callerId' => $callerId,
                'numberOrClient' => $numberOrClient
        ]);

    }

    public function newCall(Request $request)
    {
        $response = new Twiml();
        $callerIdNumber = $_ENV['TWILIO_FROM'];

        $dial = $response->dial(['callerId' => $callerIdNumber]);

        $phoneNumberToDial = $request->input('phoneNumber');

        if (isset($phoneNumberToDial)) {
            $dial->number($phoneNumberToDial);
        } else {
            $dial->client('support_agent');
        }

        return $response;
    }


}
