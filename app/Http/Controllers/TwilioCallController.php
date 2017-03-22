<?php

namespace App\Http\Controllers;

use App\Enrollee;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Twilio\Jwt\ClientToken;
use Twilio\Twiml;
use Twilio\Rest\Client;


class TwilioCallController extends Controller
{

    private $capability;
    private $token;

    public function __construct(Request $request)
    {

        $this->capability = new ClientToken($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);
        $this->capability->allowClientOutgoing($_ENV['TWILIO_ENROLLMENT_TWIML_APP_SID']);
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

        $response = new Twiml();
//        $callerIdNumber = $_ENV['TWILIO_FROM'];
        $callerIdNumber = "+17046664445";

        $dial = $response->dial(['callerId' => $callerIdNumber]);

        $phoneNumberToDial = $request->input('phoneNumber');

        $dial->number($phoneNumberToDial);

        return $response;
    }

    public function sendTestSMS(){

        // Step 3: instantiate a new Twilio Rest Client
        $client = new Client($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);

        $smsQueue = Enrollee::toSMS()->get();

        foreach ($smsQueue as $recipient){

            $provider_name = User::find($recipient->provider_id)->fullName;

            if($recipient->invite_sent_at == null){
                //first go, make invite code:

                $recipient->invite_code = rand(183,982) . substr(uniqid(), -3);
                $link = url("join/$recipient->invite_code");
                $recipient->invite_sent_at = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count = 1;
                $recipient->save();

                $message = "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link";

                $client->account->messages->create(

                // the number we are sending to - Any phone number
                    $recipient->cell_phone,

                    array(

                        'from' => "+17046664445",
                        'body' => $message,
                    )
                );

            } else {

                $sad_face_emoji = "\u{1F648}";

                $link = url("join/$recipient->invite_code");
                $recipient->invite_sent_at = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count = 2;

                $message = "Dr. $provider_name hasn’t heard from you regarding their new wellness program. $sad_face_emoji Please enroll here: $link";

                $client->account->messages->create(

                // the number we are sending to - Any phone number
                    $recipient->cell_phone,

                    array(

                        'from' => "+17046664445",
                        'body' => $message,
                    )
                );

            }

        }


    }


}
