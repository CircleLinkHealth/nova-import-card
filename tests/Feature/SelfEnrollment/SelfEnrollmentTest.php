<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\Channels\CustomTwilioChannel;
use App\Notifications\SelfEnrollmentInviteNotification;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use Illuminate\Support\Facades\Mail;
use Notification;
use PrepareDataForReEnrollmentTestSeeder;
use Tests\Concerns\TwilioFake\Twilio;

class SelfEnrollmentTest extends TestCase
{
    /**
     * Helper to create fake Enrollees.
     *
     * @var PrepareDataForReEnrollmentTestSeeder
     */
    private $factory;

    public function tests_it_does_not_send_sms_if_only_email_selected()
    {
        $this->createEnrollees($number = 2);
        Twilio::fake();
        Mail::fake();

        InvitePracticeEnrollees::dispatch(
            $number,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['mail']
        );

        Twilio::assertNothingSent();
    }

    public function tests_it_sends_enrollment_notifications()
    {
        $this->createEnrollees($number = 2);
        Notification::fake();
        InvitePracticeEnrollees::dispatch(
            $number,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['mail', CustomTwilioChannel::class]
        );
        Notification::assertTimesSent($number, SelfEnrollmentInviteNotification::class);
    }

    public function tests_it_sends_enrollment_sms()
    {
        $this->createEnrollees($number = 2);
        Twilio::fake();
        InvitePracticeEnrollees::dispatch(
            $number,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            [CustomTwilioChannel::class]
        );
        Twilio::assertNumberOfMessagesSent($number);
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
            $this->factory = $this->app->make(PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->factory;
    }
}
