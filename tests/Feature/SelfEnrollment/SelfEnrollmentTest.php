<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use App\SelfEnrollment\Helpers;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use App\SelfEnrollment\Jobs\SendInvitation;
use App\SelfEnrollment\Jobs\SendReminder;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Notification;
use PrepareDataForReEnrollmentTestSeeder;
use Tests\Concerns\TwilioFake\Twilio;

class SelfEnrollmentTest extends TestCase
{
    use  SelfEnrollableTrait;

    const COMPLETED   = 'completed';
    const IN_PROGRESS = 'in_progress';
    const PENDING     = 'pending';
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

    public function test_it_only_counts_reminders_sent_after_invitation()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->fresh()->user;
        Twilio::fake();
        Mail::fake();

        \DB::table('notifications')->insert([
            'notifiable_type' => User::class,
            'notifiable_id'   => $patient->id,
            'type'            => SelfEnrollmentInviteNotification::class,
            'data'            => json_encode([
                'enrollee_id'    => $enrollee->id,
                'is_reminder'    => true,
                'is_survey_only' => true,
            ]),
            'created_at' => now()->subDays(2)->toDateTimeString(),
            'updated_at' => now()->subDays(2)->toDateTimeString(),
        ]);
        $invitationBatch = EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id);
        SendInvitation::dispatchNow($patient, $invitationBatch->id);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
    }

    public function test_it_only_sends_one_reminder_to_non_responding_enrollee()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->fresh()->user;
        Twilio::fake();
        Mail::fake();

        SendInvitation::dispatchNow($patient);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        //It should not show up on the list on the "needs reminder" list of patients we invited yesterday
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now()->subDay())->where('id', $patient->id)->exists());

        //It should show up on the list on the "needs reminder" list of patients we invited today
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
        SendReminder::dispatchNow($patient);
        //It should not show up because we just sent a reminder
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());

        //SendReminder should not run if called again if a notification was sent
        self::assertFalse(with(new SendReminder($patient))->shouldRun());
    }

    public function test_it_saves_different_enrollment_link_in_db_when_sending_reminder()
    {
        $enrollee = $this->createEnrollees($number = 1);
        $patient  = $enrollee->fresh()->user;

        Notification::fake();
        SendInvitation::dispatchNow($patient);

        Queue::fake();

        SendInvitation::dispatch($patient);

        Queue::assertPushed(SendInvitation::class, function (SendInvitation $job) {
            Notification::fake();
            $job->handle();
            $this->assertDatabaseHas('enrollables_invitation_links', [
                'url'              => $job->getLink(),
                'manually_expired' => false,
            ]);

            return true;
        });

        self::assertTrue(2 === $count = $enrollee->enrollmentInvitationLinks()->count(), "Failed to assert that count[$count] matches the expected 2.");
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

    public function test_patient_has_clicked_get_my_care_coach()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->fresh()->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::PENDING);
        self::assertTrue(Helpers::awvUserSurveyQuery($patient, $surveyInstance)->exists());
    }

    public function test_patient_has_requested_info()
    {
        $enrollee = $this->createEnrollees(1);
        $enrollee->enrollableInfoRequest()->create();
        $this->assertDatabaseHas('enrollees_request_info', [
            'enrollable_id'   => $enrollee->id,
            'enrollable_type' => get_class($enrollee),
        ]);
    }

    public function test_patient_has_survey_completed()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->fresh()->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::COMPLETED);
        self::assertTrue(self::COMPLETED === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_survey_in_progress()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->fresh()->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::IN_PROGRESS);
        self::assertTrue(self::IN_PROGRESS === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_viewed_login_form()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->fresh()->user;
        Notification::fake();
        Mail::fake();
        SendInvitation::dispatch($patient);
        $lastEnrollmentLink = $enrollee->getLastEnrollmentInvitationLink();
        // means the patient has clicked the link and seen login form
        $lastEnrollmentLink->manually_expired = true;
        $lastEnrollmentLink->save();

        self::assertTrue(optional($enrollee->enrollmentInvitationLinks())->where('manually_expired', true)->exists());
    }

    public function test_remind_enrollees()
    {
        $enrollee = $this->createEnrollees(1);
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
