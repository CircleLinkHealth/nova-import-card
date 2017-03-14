<?php

namespace App\Algorithms\Enrollment;

use Aloha\Twilio\Twilio;
use App\Enrollee;
use App\User;
use Carbon\Carbon;


/** PhpStorm.
 * User: RohanM
 * Date: 3/14/17
 * Time: 10:21 AM
 */
class EnrollmentSMSSender
{

    private $twilio;

    public function __construct()
    {
        $this->twilio = new Twilio(
                            env('TWILIO_SID'),
                            env('TWILIO_TOKEN'),
                            env('TWILIO_FROM')
        );

    }

    public function exec(){

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

                $this->twilio->message($recipient->cell_phone, $message);

            } else {

                $sad_face_emoji = "\u{1F614}";

                $link = url("join/$recipient->invite_code");
                $recipient->invite_sent_at = Carbon::now()->toDateTimeString();
                $recipient->last_attempt_at = Carbon::now()->toDateTimeString();
                $recipient->attempt_count = 2;

                $message = "Dr. $provider_name hasn’t heard from you regarding their new wellness program. $sad_face_emoji Please enroll here: $link";

                $this->twilio->message($recipient->cell_phone, $message);

            }

        }

        return $smsQueue;

    }



}