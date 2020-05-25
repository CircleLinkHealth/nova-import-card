<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Jobs\SendSelfEnrollmentInvitationToPracticeEnrollees;
use App\Notifications\Channels\CustomTwilioChannel;
use App\Notifications\SelfEnrollmentInviteNotification;
use Illuminate\Support\Facades\Mail;
use Notification;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class SelfEnrollmentTest extends CustomerTestCase
{
    private $factory;

    public function tests_it_does_not_send_sms_if_only_email_selected()
    {
        $this->createEnrollees($number = 2);
        Twilio::fake();
        Mail::fake();
        SendSelfEnrollmentInvitationToPracticeEnrollees::dispatchNow($number, $this->practice()->id, SelfEnrollmentController::DEFAULT_BUTTON_COLOR, ['mail']);
        Twilio::assertNothingSent();
    }

    public function tests_it_sends_enrollment_notifications()
    {
        $this->createEnrollees($number = 2);
        Notification::fake();
        SendSelfEnrollmentInvitationToPracticeEnrollees::dispatchNow($number, $this->practice()->id, SelfEnrollmentController::DEFAULT_BUTTON_COLOR, ['mail', CustomTwilioChannel::class]);
        Notification::assertTimesSent($number, SelfEnrollmentInviteNotification::class);
    }

    public function tests_it_sends_enrollment_sms()
    {
        $this->createEnrollees($number = 2);
        Twilio::fake();
        SendSelfEnrollmentInvitationToPracticeEnrollees::dispatchNow($number, $this->practice()->id, SelfEnrollmentController::DEFAULT_BUTTON_COLOR, [CustomTwilioChannel::class]);
        Twilio::assertNumberOfMessageSent($number);
    }

    private function createEnrollees(int $number = 1)
    {
        if (1 === $number) {
            return $this->factory()->createEnrollee($this->practice());
        }

        $coll = collect();

        for ($i = 0; $i < $number; ++$i) {
            $coll->push($this->factory()->createEnrollee($this->practice()));
        }

        return $coll;
    }

    private function factory()
    {
        if (is_null($this->factory)) {
            $this->factory = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->factory;
    }
}
