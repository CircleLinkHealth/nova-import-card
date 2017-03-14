<?php

namespace App\Http\Controllers\Enrollment;

use Aloha\Twilio\Twilio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class EnrollmentSMSController extends Controller
{

    public function handleIncoming(Request $request){

        $sender = new Twilio(
            env('TWILIO_SID'),
            env('TWILIO_TOKEN'),
            env('TWILIO_FROM')
        );

        $sender->message('+19727622642', 'We receieved a message!');

        Log::message($request);

    }

}
