<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Jobs\LogSuccessfulLoginToDB;
use App\LoginLogout;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use App\SelfEnrollment\Domain\RemindEnrollees;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\SelfEnrollment\Helpers;
use App\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use App\SelfEnrollment\Jobs\SendInvitation;
use App\SelfEnrollment\Jobs\SendReminder;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\SelfEnrollableTrait;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Auth;
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
        self::assertTrue( ! is_null($enrollee->user_id));
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
        $patient  = $enrollee->user;
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
        $patient  = $enrollee->user;
        Twilio::fake();
        Mail::fake();

        SendInvitation::dispatchNow($patient, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        //It should not show up on the list on the "needs reminder" list of patients we invited yesterday
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now()->subDay())->where('id', $patient->id)->exists());

        //It should show up on the list on the "needs reminder" list of patients we invited today
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
        SendReminder::dispatchNow($patient);
        //It should not show up because we just sent a reminder
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());

        //SendReminder should be allowed to run one more time to send a second reminder
        self::assertFalse(with(new SendReminder($patient))->shouldRun());
    }

    public function test_it_saves_different_enrollment_link_in_db_when_sending_reminder()
    {
        $enrollee = $this->createEnrollees($number = 1);
        $patient  = $enrollee->user;

        Notification::fake();
        SendInvitation::dispatchNow($patient, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);

        Queue::fake();

        SendInvitation::dispatch($patient, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);

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

    public function test_it_sends_first_and_second_reminder_to_enrollees_and_then_takes_final_action()
    {
        $toMarkAsInvited = $this->createEnrollees($numberOfInvites = 3);

        Mail::fake();
        Twilio::fake();
        $toMarkAsInvited->each(function (Enrollee $enrollee) {
            SendInvitation::dispatchNow($enrollee->user, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);
        });

        $initialInviteSentAt  = now();
        $firstReminderSentAt  = $initialInviteSentAt->copy()->addDays(2);
        $secondReminderSentAt = $firstReminderSentAt->copy()->addDays(2);
        $finalActionRunsAt    = $secondReminderSentAt->copy()->addDays(2);

        Carbon::setTestNow($firstReminderSentAt);
        RemindEnrollees::dispatchNow($initialInviteSentAt, $toMarkAsInvited->first()->practice_id);

        //one patient enrolled after first reminder
        //pull last element in the array
        $enrolled          = $toMarkAsInvited->pull($numberOfInvites - 1);
        $expectedReminders = $numberOfInvites - 1;
        $this->assertTrue($toMarkAsInvited->count() === $expectedReminders);
        $enrolled->status = Enrollee::ENROLLED;
        $enrolled->save();

        Carbon::setTestNow($secondReminderSentAt);
        RemindEnrollees::dispatchNow($initialInviteSentAt, $toMarkAsInvited->first()->practice_id);

        $toMarkAsInvited->each(function ($enrollee) use ($firstReminderSentAt, $secondReminderSentAt) {
            $this->assertDatabaseHas('enrollees', [
                'id'                        => $enrollee->id,
                'status'                    => Enrollee::QUEUE_AUTO_ENROLLMENT,
                'auto_enrollment_triggered' => false,
            ]);

            $this->assertTrue(
                $enrollee->user
                    ->hasSelfEnrollmentInviteReminder($firstReminderSentAt)
                    ->hasSelfEnrollmentInviteReminder($secondReminderSentAt)
                    ->exists()
            );
        });

        Carbon::setTestNow($finalActionRunsAt);
        UnreachablesFinalAction::dispatchNow($initialInviteSentAt, $toMarkAsInvited->first()->practice_id);

        $toMarkAsInvited->each(function ($enrollee) {
            $this->assertDatabaseHas('enrollees', [
                'id'                        => $enrollee->id,
                'status'                    => Enrollee::TO_CALL,
                'auto_enrollment_triggered' => true,
            ]);
        });
    }

    public function test_it_sends_first_reminder_to_enrollees()
    {
        //enrollees who requested call
        $requestedInfo = $this->createEnrollees(1);
        $requestedInfo->each(function (Enrollee $enrollee) {
            $enrollee->enrollableInfoRequest()->create();
        });

        $notInvitedYet = $this->createEnrollees(2);

        $expectedReminders = 3;
        $toMarkAsInvited   = $this->createEnrollees($expectedReminders);

        Mail::fake();
        Twilio::fake();
        $toMarkAsInvited->each(function (Enrollee $enrollee) {
            SendInvitation::dispatchNow($enrollee->user, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);
        });

        Queue::fake();

        RemindEnrollees::dispatchNow(now()->startOfDay(), $toMarkAsInvited->first()->practice_id);

        Queue::assertPushed(SendReminder::class, $expectedReminders);
        $remindersUserIds = $toMarkAsInvited->pluck('user_id')->all();
        Queue::assertPushed(SendReminder::class, function (SendReminder $job) use ($remindersUserIds) {
            $this->assertTrue($result = in_array($job->patient->id, $remindersUserIds), $job->patient->id.' was not founf in .'.implode(',', $remindersUserIds));

            return $result;
        });
    }

    public function test_patient_has_clicked_get_my_care_coach()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::PENDING);
        self::assertTrue(Helpers::awvUserSurveyQuery($patient, $surveyInstance)->exists());
    }

    public function test_patient_has_logged_in()
    {
        $enrollee = $this->createEnrollees(1);

        Queue::fake();
        Auth::loginUsingId($enrollee->user_id);

        Queue::assertPushed(LogSuccessfulLoginToDB::class, function (LogSuccessfulLoginToDB $job) use ($enrollee) {
            $job->handle();

            return LoginLogout::whereUserId($enrollee->user_id)->exists();
        });
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
        $patient        = $enrollee->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::COMPLETED);
        self::assertTrue(self::COMPLETED === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_survey_in_progress()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = Helpers::createSurveyConditionsAndGetSurveyInstance($patient->id, self::IN_PROGRESS);
        self::assertTrue(self::IN_PROGRESS === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_viewed_login_form()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->user;
        Notification::fake();
        Mail::fake();
        SendInvitation::dispatch($patient, EnrollmentInvitationsBatch::manualInvitesBatch($enrollee->practice_id)->id);
        $lastEnrollmentLink = $enrollee->getLastEnrollmentInvitationLink();
        // means the patient has clicked the link and seen login form
        $lastEnrollmentLink->manually_expired = true;
        $lastEnrollmentLink->save();

        self::assertTrue(optional($enrollee->enrollmentInvitationLinks())->where('manually_expired', true)->exists());
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
