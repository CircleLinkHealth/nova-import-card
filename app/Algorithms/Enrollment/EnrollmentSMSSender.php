<?php

namespace App\Algorithms\Enrollment;

use Aloha\Twilio\Twilio;
use App\User;


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

        $smsQueue = \App\Enrollee::toSMS()->get();

        foreach ($smsQueue as $recipient){

            if($recipient->invite_sent_at == null){
                //first go

                $link = url("join/$recipient->invite_code");
                $provider_name = User::find($recipient->provider_id)->fullName;
                $message = "Dr. $provider_name has invited you to their new wellness program! Please enroll here: $link";

                //        $twilio->message($enrollee->phone, $message);


            }

        }

        return $smsQueue;




    }



}