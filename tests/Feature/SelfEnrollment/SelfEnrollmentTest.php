<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use App\SelfEnrollment\Jobs\SendInvitation;
use App\SelfEnrollment\Jobs\SendReminder;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
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

    public function test_it_creates_user_from_enrollee()
    {
        $enrollee = $this->createEnrollees();
        CreateSurveyOnlyUserFromEnrollee::dispatch($enrollee);
        self::assertTrue( ! is_null($enrollee->fresh()->user_id));
    }

    public function test_it_does_not_send_sms_if_only_email_selected()
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

    public function test_it_saves_enrollment_link_in_db_when_sending_invite()
    {
        $this->createEnrollees($number = 1);

        Queue::fake();

        InvitePracticeEnrollees::dispatchNow(
            $number,
            $this->practice()->id,
            $color = SelfEnrollmentController::RED_BUTTON_COLOR,
            ['mail', CustomTwilioChannel::class]
        );

        Queue::assertPushed(SendInvitation::class, function (SendInvitation $job) use ($color) {
            Notification::fake();
            $job->handle();
            $this->assertDatabaseHas('enrollables_invitation_links', [
                'url'              => $job->getLink(),
                'manually_expired' => false,
                'button_color'     => $color,
            ]);

            return true;
        });
    }

    public function test_it_sends_enrollment_notifications()
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

    public function test_it_sends_enrollment_sms()
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

    public function test_it_sends_one_reminder_to_non_responding_enrollee()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->fresh()->user;
        Twilio::fake();
        Mail::fake();

        SendInvitation::dispatchNow($patient);
        self::assertTrue(User::wasSentSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        self::assertTrue(User::enrollableUsersToRemind(now(), now()->subDays(2))->where('id', $patient->id)->exists());

        SendReminder::dispatchNow($patient);
        self::assertFalse(User::enrollableUsersToRemind(now(), now()->subDays(2))->where('id', $patient->id)->exists());

        //SendReminder should not run if called again if a notification was sent
        self::assertFalse(with(new SendReminder($patient))->shouldRun());
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
