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



    }



}