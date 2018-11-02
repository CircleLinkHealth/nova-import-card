<?php

namespace App\Http\Controllers;

use App\CLH\Helpers\StringManipulation;
use App\Enrollee;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Jwt\ClientToken;
use Twilio\Twiml;
use Twilio\Rest\Client;

class TwilioController extends Controller
{

    private $capability;
    private $token;

    public function __construct(Request $request)
    {

        $this->capability = new ClientToken(config('services.twilio.sid'), config('services.twilio.token'));
        $this->capability->allowClientOutgoing(config('services.twilio.enrollment-app-sid'));
        $this->token = $this->capability->generateToken();
    }

    public function obtainToken()
    {

        return response()->json(['token' => $this->capability->generateToken()]);
    }

    public function makeCall()
    {

        return view(
            'partials.calls.make-twilio-call',
            [
                'token' => $this->token,
            ]
        );
    }

    public function newCall(Request $request)
    {

        $response = new Twiml();

        $phoneNumberToDial = (new StringManipulation())->formatPhoneNumberE164($request->input('phoneNumber'));

        $enrollee = Enrollee::where(function ($q) use ($phoneNumberToDial) {
            $q->where('cell_phone', $phoneNumberToDial)
                ->orWhere('home_phone', $phoneNumberToDial)
                ->orWhere('other_phone', $phoneNumberToDial);
        })->first();

        $practiceId = $enrollee['practice_id'];

        $callerIdNumber = optional(Practice::find($practiceId))->outgoing_phone_number;

        if ($callerIdNumber){
            $dial = $response->dial(['callerId' => $callerIdNumber]);

            $dial->number($phoneNumberToDial);

            return $response;
        }

        throw new \Exception("Practice Outgoing Phone Number not found.", 500);


    }

    public function sendTestSMS()
    {

        $client = new Client($_ENV['TWILIO_SID'], $_ENV['TWILIO_TOKEN']);

        $smsQueue = Enrollee::toSMS()->get();

        foreach ($smsQueue as $recipient) {
            $provider_name = User::find($recipient->provider_id)->getFullName();

            if ($recipient->invite_sent_at == null) {
                //first go, make invite code:

                $recipient->invite_code = rand(183, 982) . substr(uniqid(), -3);
                $link = url("join/$recipient->invite_code");
                $recipient->invite_sent_at = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count = 1;
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

                $link = url("join/$recipient->invite_code");
                $recipient->invite_sent_at = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count = 2;
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
}
