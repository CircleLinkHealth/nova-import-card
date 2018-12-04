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
            config('services.twilio.sid'),
            config('services.twilio.token'),
            config('services.twilio.from')
        );
    }

    public function exec()
    {
    }
}
