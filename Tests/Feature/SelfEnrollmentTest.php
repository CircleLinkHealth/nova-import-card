<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Tests\Feature;

use CircleLinkHealth\Core\Jobs\LogSuccessfulLoginToDB;
use CircleLinkHealth\SelfEnrollment\Constants;
use CircleLinkHealth\SelfEnrollment\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\SelfEnrollment\Traits\EnrollableNotificationContent;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Constants\ProviderClinicalTypes;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use CircleLinkHealth\SelfEnrollment\AppConfig\Reminders;
use CircleLinkHealth\SelfEnrollment\Console\Commands\PrepareDataForReEnrollmentTestSeeder;
use CircleLinkHealth\SelfEnrollment\Domain\InvitePracticeEnrollees;
use CircleLinkHealth\SelfEnrollment\Domain\RemindEnrollees;
use CircleLinkHealth\SelfEnrollment\Domain\UnreachablesFinalAction;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationsBatch;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use CircleLinkHealth\SelfEnrollment\Jobs\CreateSurveyOnlyUserFromEnrollee;
use CircleLinkHealth\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\SelfEnrollment\Jobs\SendReminder;
use CircleLinkHealth\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\LoginLogout;
use CircleLinkHealth\TwilioIntegration\Notifications\Channels\CustomTwilioChannel;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Core\Tests\Concerns\TwilioFake\Twilio;
use CircleLinkHealth\SelfEnrollment\Tests\TestCase;

class SelfEnrollmentTest extends TestCase
{
    use EnrollableNotificationContent;
    use WithFaker;
    /**
     * @var
     */
    private $factory;

    public function test_it_creates_batch()
    {
        $enrollee = $this->createEnrollees(1);
        $type     = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;
        $batch    = EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            $type
        );

        $this->assertDatabaseHas('enrollment_invitations_batches', [
            'id'          => $batch->id,
            'practice_id' => $enrollee->practice_id,
            'type'        => $type,
        ]);
    }

    public function test_it_creates_one_batch_for_each_button_color_in_one_hour_range()
    {
        $enrollees = $this->createEnrollees(3);
        $type      = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        foreach ($enrollees->take(1) as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $type
            );
        }

        $type = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.SelfEnrollmentController::RED_BUTTON_COLOR;
        foreach ($enrollees->skip(1)->take(1) as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $type
            );
        }

        $type = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.SelfEnrollmentController::RED_BUTTON_COLOR;
        foreach ($enrollees->skip(2)->take(1) as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $type
            );
        }

        static::assertTrue(2 === EnrollmentInvitationsBatch::where('practice_id', $enrollee->practice_id)->count());
    }

    public function test_it_creates_one_batch_for_each_hour_sent()
    {
        $enrollees = $this->createEnrollees($num = 2);
        $n         = 0;
        foreach ($enrollees as $enrollee) {
            $type = now()->addHours($n)->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $type
            );
            ++$n;
        }

        $this->assertTrue($num === EnrollmentInvitationsBatch::where('practice_id', $enrollee->practice_id)->count());
    }

    public function test_it_creates_one_batch_in_one_hour_range()
    {
        $enrollees = $this->createEnrollees($num = 2);
        $type      = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;
//        Attempt to create 2 batches
        foreach ($enrollees as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $type
            );
        }

        $this->assertTrue(1 === EnrollmentInvitationsBatch::where('practice_id', $enrollee->practice_id)->count());
    }

    public function test_it_creates_seperate_batches_for_random_and_manual_invites()
    {
        $enrollees  = $this->createEnrollees($num = 2);
        $typeManual = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE;

        foreach ($enrollees->take(1) as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $typeManual
            );
        }

        $typeRandom = now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.SelfEnrollmentController::DEFAULT_BUTTON_COLOR;
        foreach ($enrollees->skip(1)->take(1) as $enrollee) {
            EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                $typeRandom
            );
        }
        $this->assertDatabaseHas('enrollment_invitations_batches', [
            'practice_id' => $enrollee->practice_id,
            'type'        => $typeManual,
        ]);
        $this->assertDatabaseHas('enrollment_invitations_batches', [
            'practice_id' => $enrollee->practice_id,
            'type'        => $typeRandom,
        ]);
    }

    public function test_it_creates_user_from_enrollee()
    {
        $enrollee = $this->createEnrollees();
//        Will trigger EnrolleeObserver to Create User
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

       $this->setFakeNotification($patient->id, now()->subMonth()->toDateTimeString(), $enrollee->id);

       $invitationBatch = EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        );
        SendInvitation::dispatchNow(new User($patient->toArray()), $invitationBatch->id);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
    }

    public function test_it_only_sends_one_reminder_to_non_responding_enrollee()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->user;
        Twilio::fake();
        Mail::fake();

        SendInvitation::dispatchNow(new User($patient->toArray()), EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        //It should not show up on the list on the "needs reminder" list of patients we invited yesterday
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now()->subDay())->where('id', $patient->id)->exists());

        //It should show up on the list on the "needs reminder" list of patients we invited today
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
        SendReminder::dispatchNow(new User($patient->toArray()));
        //It should not show up because we just sent a reminder
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());

        //SendReminder should be allowed to run one more time to send a second reminder
        self::assertTrue(with(new SendReminder(new User($patient->toArray())))->shouldRun());
    }

    public function test_it_removes_email_channel_if_fake_email()
    {
        $enrollee = $this->createEnrollees();

        $enrollee->user->email = 'test@careplanmanager.com';
        $enrollee->user->save();

        $this->assertFalse(in_array('mail', (new SelfEnrollmentInviteNotification('hello'))->via($enrollee->user)));
    }

    public function test_it_returns_false_if_key_practice_disable_self_enrolment_reminders_set()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient  = $enrollee->user;
        $practice = $patient->primaryPractice;
        $this->disableReminders($practice->name);
        self::assertFalse(Reminders::areEnabledFor($practice->name));
    }

    public function test_it_saves_different_enrollment_link_in_db_when_sending_reminder()
    {
        $enrollee = $this->createEnrollees($number = 1);
        $patient  = new User($enrollee->user->toArray());

        Notification::fake();
        SendInvitation::dispatchNow($patient, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);

        Queue::fake();

        SendInvitation::dispatch($patient, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);

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
            $color = SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['mail', 'twilio']
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
            ['mail', 'twilio']
        );

        Notification::assertTimesSent($number, SelfEnrollmentInviteNotification::class);
    }

    public function test_it_sends_enrollment_notifications_limited()
    {
        $this->createEnrollees($number = 5);
        Notification::fake();
        InvitePracticeEnrollees::dispatch(
            $limit = 2,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['mail', CustomTwilioChannel::class]
        );
        Notification::assertTimesSent($limit, SelfEnrollmentInviteNotification::class);
    }

    public function test_it_sends_enrollment_sms()
    {
        $this->createEnrollees($number = 2);
        Twilio::fake();
        InvitePracticeEnrollees::dispatch(
            $number,
            $this->practice()->id,
            SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            ['twilio']
        );
        Twilio::assertNumberOfMessagesSent($number);
    }

    public function test_it_sends_first_and_second_reminder_to_enrollees_and_then_takes_final_action()
    {
        $toMarkAsInvited = $this->createEnrollees($numberOfInvites = 3);

        Mail::fake();
        Twilio::fake();
        $toMarkAsInvited->each(function (Enrollee $enrollee) {
            SendInvitation::dispatchNow(new User($enrollee->user->toArray()), EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
            )->id);
        });

        $initialInviteSentAt  = now();
        $firstReminderSentAt  = $initialInviteSentAt->copy()->addDays(Constants::DAYS_AFTER_FIRST_INVITE_TO_SEND_FIRST_REMINDER);
        $secondReminderSentAt = $initialInviteSentAt->copy()->addDays(Constants::DAYS_AFTER_FIRST_INVITE_TO_SEND_SECOND_REMINDER);
        $finalActionRunsAt    = $initialInviteSentAt->copy()->addDays(Constants::DAYS_DIFF_FROM_FIRST_INVITE_TO_FINAL_ACTION);

        Carbon::setTestNow($firstReminderSentAt);
        RemindEnrollees::dispatchNow($initialInviteSentAt, $toMarkAsInvited->first()->practice_id);

        //one patient enrolled after first reminder
        //pull last element in the array
        $enrolled          = $toMarkAsInvited->pull($numberOfInvites - 1);
        $expectedReminders = $numberOfInvites - 1;
        $this->assertTrue($toMarkAsInvited->count() === $expectedReminders);
        $enrolled->status = Enrollee::ENROLLED;
        $enrolled->save();

        //Assert it won't take final action before second reminder has been sent
        Carbon::setTestNow($secondReminderSentAt);
        UnreachablesFinalAction::dispatchNow($initialInviteSentAt, $toMarkAsInvited->first()->practice_id);
        $toMarkAsInvited->each(function ($enrollee) {
            $this->assertDatabaseHas('enrollees', [
                'id'                        => $enrollee->id,
                'status'                    => Enrollee::QUEUE_AUTO_ENROLLMENT,
                'auto_enrollment_triggered' => false,
            ]);
        });

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
            SendInvitation::dispatchNow(new User($enrollee->user->toArray()), EnrollmentInvitationsBatch::firstOrCreateAndRemember(
                $enrollee->practice_id,
                now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
            )->id);
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

    public function test_it_will_not_send_reminder_if_disable_config_is_on()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient  = $enrollee->user;
        $practice = $patient->primaryPractice;
        $this->disableReminders($practice->name);
        self::assertFalse((new SendReminder(new User($patient->toArray())))->shouldRun());
        self::assertFalse(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
    }

    public function test_it_will_send_invitation_if_disable_config_is_on()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient  = $enrollee->user;
        $practice = $patient->primaryPractice;
        $this->disableReminders($practice->name);

        SendInvitation::dispatchNow(new User($patient->toArray()), EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);

        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
    }

    public function test_it_will_show_the_correct_medical_type_do_suffix_on_letter()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient                                = $enrollee->user;
        $billingProviderUser = $patient->billingProviderUser();
        $billingProviderUser->suffix = ProviderClinicalTypes::DO_SUFFIX;
        $patient->fresh();
        $patient->save();
        $billingProviderUser->save();
        $specialty = Helpers::providerMedicalType($billingProviderUser->suffix);

        self::assertTrue(ProviderClinicalTypes::DR === $specialty);

        $emailContent     = $this->getEnrolleeMessageContent(new User($patient->toArray()), false);
        $providerLastName = $emailContent['providerLastName'];
        $nameWithType     = "$specialty $providerLastName";

        self::assertTrue(Str::contains($emailContent['line2'], $nameWithType));
    }

    public function test_it_will_show_the_correct_medical_type_lpn_suffix_on_letter()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient                                = $enrollee->user;
        $billingProviderUser = $patient->billingProviderUser();
        $billingProviderUser->suffix = ProviderClinicalTypes::LPN_SUFFIX;
        $patient->fresh();
        $patient->save();
        $billingProviderUser->save();
        $specialty = Helpers::providerMedicalType($billingProviderUser->suffix);
        self::assertTrue(ProviderClinicalTypes::LPN === $specialty);

        $patient = new User($patient->toArray());

        $emailContent     = $this->getEnrolleeMessageContent($patient, false);
        $providerLastName = $emailContent['providerLastName'];
        $nameWithType     = "$specialty $providerLastName";

        self::assertTrue(Str::contains($emailContent['line2'], $nameWithType));
    }

    public function test_it_will_show_the_correct_medical_type_md_suffix_on_letter()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient                                = $enrollee->user;
        $billingProviderUser = $patient->billingProviderUser();
        $billingProviderUser->suffix = ProviderClinicalTypes::MD_SUFFIX;
        $patient->fresh();
        $patient->save();
        $billingProviderUser->save();
        $specialty = Helpers::providerMedicalType($billingProviderUser->suffix);

        self::assertTrue(ProviderClinicalTypes::DR === $specialty);

        $emailContent     = $this->getEnrolleeMessageContent(new User($patient->toArray()), false);
        $providerLastName = $emailContent['providerLastName'];
        $nameWithType     = "$specialty $providerLastName";

        self::assertTrue(Str::contains($emailContent['line2'], $nameWithType));
    }

    public function test_it_will_show_the_correct_medical_type_np_suffix_on_letter()
    {
        $enrollee = $this->createEnrollees($number = 1);
        /** @var User $patient */
        $patient                                = $enrollee->user;
        $billingProviderUser = $patient->billingProviderUser();
        $billingProviderUser->suffix = ProviderClinicalTypes::NP_SUFFIX;
        $patient->fresh();
        $patient->save();
        $billingProviderUser->save();
        $specialty = Helpers::providerMedicalType($billingProviderUser->suffix);
        self::assertTrue(ProviderClinicalTypes::NP === $specialty);

        $emailContent     = $this->getEnrolleeMessageContent(new User($patient->toArray()), false);
        $providerLastName = $emailContent['providerLastName'];
        $nameWithType     = "$specialty $providerLastName";

        self::assertTrue(Str::contains($emailContent['line2'], $nameWithType));
    }

    public function test_patient_has_clicked_get_my_care_coach()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_PENDING);
        self::assertTrue(Helpers::awvUserSurveyQuery(new User($patient->toArray()), $surveyInstance)->exists());
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
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_COMPLETED);
        self::assertTrue(SelfEnrollmentController::ENROLLMENT_SURVEY_COMPLETED === Helpers::awvUserSurveyQuery(new User($patient->toArray()), $surveyInstance)->first()->status);
    }

    public function test_patient_has_survey_in_progress()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_IN_PROGRESS);
        self::assertTrue(SelfEnrollmentController::ENROLLMENT_SURVEY_IN_PROGRESS === Helpers::awvUserSurveyQuery(new User($patient->toArray()), $surveyInstance)->first()->status);
    }

    public function test_patient_has_viewed_login_form()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->user;
        Notification::fake();
        Mail::fake();
        SendInvitation::dispatch(new User($patient->toArray()), EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);
        $lastEnrollmentLink = $enrollee->getLastEnrollmentInvitationLink();
        // means the patient has clicked the link and seen login form
        $lastEnrollmentLink->manually_expired = true;
        $lastEnrollmentLink->save();

        self::assertTrue(optional($enrollee->enrollmentInvitationLinks())->where('manually_expired', true)->exists());
    }

    private function createEnrollees(int $number = 1, array $arguments = [])
    {
        if (1 === $number) {
            return $this->factory()->createEnrollee($this->practice(), $this->provider(), $arguments);
        }

        $coll = collect();

        for ($i = 0; $i < $number; ++$i) {
            $coll->push($this->factory()->createEnrollee($this->practice(), $this->provider()));
        }

        return $coll;
    }

    private function createSurveyConditions(int $userId, int $surveyInstanceId, int $surveyId, string $status)
    {
        DB::table('users_surveys')->insert(
            [
                'user_id'            => $userId,
                'survey_instance_id' => $surveyInstanceId,
                'survey_id'          => $surveyId,
                'status'             => $status,
                'start_date'         => Carbon::parse(now())->toDateTimeString(),
            ]
        );
    }

    private function createSurveyConditionsAndGetSurveyInstance(string $userId, string $status)
    {
        $surveyId = $this->firstOrCreateEnrollmentSurvey();

        $surveyInstanceId = DB::table('survey_instances')->insertGetId([
            'survey_id' => $surveyId,
            'year'      => Carbon::now(),
        ]);

        self::createSurveyConditions($userId, $surveyInstanceId, $surveyId, $status);

        return DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
    }

    private function disableReminders(string $practiceName)
    {
        AppConfig::clearCache();

        AppConfig::create(
            [
                'config_key'   => 'practice_disable_self_enrolment_reminders',
                'config_value' => $practiceName,
            ]
        );
    }

    private function factory()
    {
        if (is_null($this->factory)) {
            $this->factory = $this->app->make(PrepareDataForReEnrollmentTestSeeder::class);
        }

        return $this->factory;
    }

    private function firstOrCreateEnrollmentSurvey()
    {
        $surveyId = optional(DB::table('surveys')
            ->where('name', SelfEnrollmentController::ENROLLEES_SURVEY_NAME)
            ->first())->id;

        if ( ! $surveyId) {
            $surveyId = DB::table('surveys')
                ->insertGetId([
                    'name' => SelfEnrollmentController::ENROLLEES_SURVEY_NAME,
                ]);
        }

        DB::table('survey_instances')->insertGetId([
            'survey_id' => $surveyId,
            'year'      => now()->year,
        ]);

        return $surveyId;
    }

    public function test_it_does_not_assign_same_user_id_to_similarly_named_enrollees()
    {
        $samePersonAttributes = [
            'last_name' => $this->faker->lastName,
            'dob'=> Carbon::now()->subYears($this->faker->numberBetween(50, 100)),
            'practice_id'=> $this->practice()->id
        ];

        $enrollee1 = $this->createEnrollees(1);
        $enrollee1->update($samePersonAttributes);

        CreateSurveyOnlyUserFromEnrollee::dispatchNow($enrollee1->fresh());
        $enrollee2 = $this->createEnrollees(1);
        $enrollee2->update($samePersonAttributes);

        DB::commit();

        CreateSurveyOnlyUserFromEnrollee::dispatchNow($enrollee2->fresh());

        /** @var Enrollee $enrollee1Fresh */
        $enrollee1Fresh = $enrollee1->fresh();
        $enrollee2Fresh = $enrollee2->fresh();

        self::assertTrue($enrollee1Fresh->first_name !== $enrollee2Fresh->first_name);
        self::assertTrue(! is_null($enrollee1Fresh->user_id));
        self::assertTrue( ! is_null($enrollee2Fresh->user_id));
        self::assertTrue( $enrollee1Fresh->last_name === $enrollee2Fresh->last_name);
        self::assertTrue( $enrollee1Fresh->dob->isSameDay($enrollee2Fresh->dob));
        self::assertTrue( $enrollee1Fresh->practice_id === $enrollee2Fresh->practice_id);
        self::assertFalse( $enrollee1Fresh->user_id === $enrollee2Fresh->user_id);
    }

    public function test_it_assigns_same_user_id_to_same_person_enrollee()
    {
        $enrollee1 = $this->createEnrollees(1);
        DB::commit();
        $enrollee1User = $enrollee1->user;
        self::assertTrue($enrollee1->first_name === $enrollee1User->first_name);
        self::assertTrue($enrollee1->last_name === $enrollee1User->last_name);
        self::assertTrue($enrollee1->practice_id === $enrollee1User->program_id);

        $samePersonAttributes = [
            'first_name' => $enrollee1->first_name,
            'last_name' => $enrollee1->last_name,
            'dob' => $enrollee1->dob,
            'practice_id'=> $enrollee1->practice_id,
        ];

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);
        $enrollee2User = $enrollee2->user;

        self::assertTrue($enrollee2->first_name === $enrollee2User->first_name);
        self::assertTrue($enrollee2->last_name === $enrollee2User->last_name);
        self::assertTrue($enrollee2->practice_id === $enrollee2User->program_id);

        self::assertTrue(! is_null($enrollee1->user_id));
        self::assertTrue( ! is_null($enrollee2->user_id));

        self::assertTrue($enrollee1User->first_name === $enrollee2User->first_name);
        self::assertTrue($enrollee1User->last_name === $enrollee2User->last_name);
        self::assertTrue($enrollee1User->program_id === $enrollee2User->program_id);
        self::assertTrue($enrollee1->first_name === $enrollee2->first_name);
        self::assertTrue( $enrollee1->last_name === $enrollee2->last_name);
        self::assertTrue( $enrollee1->dob->isSameDay($enrollee2->dob));
        self::assertTrue( $enrollee1->practice_id === $enrollee2->practice_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);
    }

    public function test_it_assigns_same_user_id_to_same_person_enrollee_with_middle_name()
    {

        $enrollee1 = $this->createEnrollees(1);
        DB::commit();
        $enrollee1User = $enrollee1->user;

        self::assertTrue($enrollee1->first_name === $enrollee1User->first_name);
        self::assertTrue($enrollee1->last_name === $enrollee1User->last_name);
        self::assertTrue($enrollee1->practice_id === $enrollee1User->program_id);

        $samePersonAttributes = [
            'first_name' => "{$enrollee1->first_name} M",
            'last_name' => $enrollee1->last_name,
            'dob' => $enrollee1->dob,
            'practice_id'=> $enrollee1->practice_id,
        ];

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);
        $enrollee2User = $enrollee2->user;

        self::assertTrue($enrollee1->first_name !== $enrollee2->first_name);
        self::assertTrue(! is_null($enrollee1->user_id));
        self::assertTrue( ! is_null($enrollee2->user_id));
        self::assertTrue( $enrollee1->last_name === $enrollee2->last_name);
        self::assertTrue( $enrollee1->dob->isSameDay($enrollee2->dob));
        self::assertTrue( $enrollee1->practice_id === $enrollee2->practice_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);
    }

    public function test_it_assigns_same_user_id_to_same_person_enrollee_with_middle_name_2()
    {
        $firstName = $this->faker->firstName;
        $samePersonAttributes = [
            'last_name' => $this->faker->lastName,
            'dob' => Carbon::now()->subYears($this->faker->numberBetween(50, 100)),
            'practice_id'=> $this->practice()->id
        ];

        $samePersonAttributes['first_name'] = "$firstName, M";
        $enrollee1 = $this->createEnrollees(1, $samePersonAttributes);

        DB::commit();

        $samePersonAttributes['first_name'] = $firstName;

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);

        self::assertTrue($enrollee1->first_name !== $enrollee2->first_name);
        self::assertTrue(! is_null($enrollee1->user_id));
        self::assertTrue( ! is_null($enrollee2->user_id));
        self::assertTrue( $enrollee1->last_name === $enrollee2->last_name);
        self::assertTrue( $enrollee1->dob->isSameDay($enrollee2->dob));
        self::assertTrue( $enrollee1->practice_id === $enrollee2->practice_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);
    }

    public function test_it_assigns_same_user_id_to_same_person_enrollee_with_different_middle_name()
    {
        $firstName = $this->faker->firstName;
        $samePersonAttributes = [
            'last_name' => $this->faker->lastName,
            'practice_id'=> $this->practice()->id
        ];

        $enrollee1 = $this->createEnrollees(1);
        $samePersonAttributes['dob'] = Carbon::now()->subYears($this->faker->numberBetween(50, 100));
        $samePersonAttributes['first_name'] = "$firstName, M";
        $enrollee1->update($samePersonAttributes);

        DB::commit();

        CreateSurveyOnlyUserFromEnrollee::dispatchNow($enrollee1->fresh());
        $enrollee2 = $this->createEnrollees(1);
        $samePersonAttributes['dob'] = Carbon::now()->subYears($this->faker->numberBetween(50, 100));

        $samePersonAttributes['first_name'] = "$firstName, A";
        $enrollee2->update($samePersonAttributes);

        /** @var Enrollee $enrollee1Fresh */
        $enrollee1Fresh = $enrollee1->fresh();
        $enrollee2Fresh = $enrollee2->fresh();

        CreateSurveyOnlyUserFromEnrollee::dispatchNow($enrollee2->fresh());

        /** @var Enrollee $enrollee1Fresh */
        $enrollee1Fresh = $enrollee1->fresh();
        $enrollee2Fresh = $enrollee2->fresh();

        self::assertTrue($enrollee1Fresh->first_name !== $enrollee2Fresh->first_name);
        self::assertTrue(! is_null($enrollee1Fresh->user_id));
        self::assertTrue( ! is_null($enrollee2Fresh->user_id));
        self::assertTrue( $enrollee1Fresh->last_name === $enrollee2Fresh->last_name);
        self::assertFalse( $enrollee1Fresh->dob->isSameDay($enrollee2Fresh->dob));
        self::assertTrue( $enrollee1Fresh->practice_id === $enrollee2Fresh->practice_id);
        self::assertFalse( $enrollee1Fresh->user_id === $enrollee2Fresh->user_id);
    }

    public function test_if_duplicated_enrollee_will_not_send_invitation()
    {
        $toInvite = collect();
        $enrollee1 = $this->createEnrollees(1);
        DB::commit();
        $enrollee1User = $enrollee1->user;
        self::assertTrue($enrollee1->first_name === $enrollee1User->first_name);
        self::assertTrue($enrollee1->last_name === $enrollee1User->last_name);
        self::assertTrue($enrollee1->practice_id === $enrollee1User->program_id);

        $samePersonAttributes = [
            'first_name' => $enrollee1->first_name,
            'last_name' => $enrollee1->last_name,
            'dob' => $enrollee1->dob,
            'practice_id'=> $enrollee1->practice_id,
        ];

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);
        $enrollee2User = $enrollee2->user;

        self::assertTrue($enrollee2->first_name === $enrollee2User->first_name);
        self::assertTrue($enrollee2->last_name === $enrollee2User->last_name);
        self::assertTrue($enrollee2->practice_id === $enrollee2User->program_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);

        $toInvite->push($enrollee1, $enrollee2);

        Mail::fake();
        Twilio::fake();
        $toInvite->each(function (Enrollee $enrollee) {
            $patient = $enrollee->user;
            $this->expectException(\Exception::class);
            SendInvitation::dispatchNow(new User($patient->toArray()),
                EnrollmentInvitationsBatch::firstOrCreateAndRemember($enrollee->practice_id,
                now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
            )->id);
        });

    }

    public function test_duplicated_enrolles_on_random_invitations()
    {
        $enrollee1 = $this->createEnrollees(1);
        DB::commit();
        $enrollee1User = $enrollee1->user;
        $constPracticeId = $enrollee1->practice_id;
        self::assertTrue($enrollee1->first_name === $enrollee1User->first_name);
        self::assertTrue($enrollee1->last_name === $enrollee1User->last_name);
        self::assertTrue($enrollee1->practice_id === $enrollee1User->program_id);

        $samePersonAttributes = [
            'first_name' => $enrollee1->first_name,
            'last_name' => $enrollee1->last_name,
            'dob' => $enrollee1->dob,
            'practice_id'=> $constPracticeId,
        ];

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);
        $enrollee2User = $enrollee2->user;

        self::assertTrue($enrollee2->first_name === $enrollee2User->first_name);
        self::assertTrue($enrollee2->last_name === $enrollee2User->last_name);
        self::assertTrue($enrollee2->practice_id === $enrollee2User->program_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);
        self::assertTrue( $enrollee1->practice_id === $enrollee2->practice_id);

        Mail::fake();
        Twilio::fake();

        $this->expectException(\Exception::class);
        InvitePracticeEnrollees::dispatchNow(
            2,
            $constPracticeId,
        );
    }

    public function test_that_already_invited_duplicated_enrollees_will_not_receive_any_reminders()
    {
        $toInvite = collect();
        $enrollee1 = $this->createEnrollees(1);
        DB::commit();
        $enrollee1User = $enrollee1->user;
        self::assertTrue($enrollee1->first_name === $enrollee1User->first_name);
        self::assertTrue($enrollee1->last_name === $enrollee1User->last_name);
        self::assertTrue($enrollee1->practice_id === $enrollee1User->program_id);

        $samePersonAttributes = [
            'first_name' => $enrollee1->first_name,
            'last_name' => $enrollee1->last_name,
            'dob' => $enrollee1->dob,
            'practice_id'=> $enrollee1->practice_id,
        ];

        $enrollee2 = $this->createEnrollees(1, $samePersonAttributes);
        $enrollee2User = $enrollee2->user;

        self::assertTrue($enrollee2->first_name === $enrollee2User->first_name);
        self::assertTrue($enrollee2->last_name === $enrollee2User->last_name);
        self::assertTrue($enrollee2->practice_id === $enrollee2User->program_id);
        self::assertTrue( $enrollee1->user_id === $enrollee2->user_id);

        $toInvite->push($enrollee1, $enrollee2);
        $initialInviteSentAt  = now();
        $batchId = EnrollmentInvitationsBatch::firstOrCreateAndRemember($enrollee1->practice_id,
            $initialInviteSentAt->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id;

        $toInvite->each(function (Enrollee $enrollee) use($batchId, $initialInviteSentAt){
            $url = 'https://fake-url.com';
            $urlToken = "9002ac8f0d7b2e171a55bd33cb91907ca45805874461e92f2d1fcc60b6656b75 $enrollee->id";
            $enrollee->enrollmentInvitationLinks()->create([
               'link_token'       => $urlToken,
               'batch_id'         => $batchId,
               'url'              => $url,
               'manually_expired' => false,
               'button_color'     => SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
           ]);

            $this->setFakeNotification($enrollee->user->id, $enrollee->id, $initialInviteSentAt->copy()->startOfDay()->toDateTimeString(),false);
        });

//        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $enrollee1->user_id)->exists());
//        self::assertTrue($enrollee1->enrollmentInvitationLinks()->count() === 1);
//        self::assertTrue($enrollee2->enrollmentInvitationLinks()->count() === 1);
//
//        $firstReminderSentAt  = $initialInviteSentAt->copy()->addDays(Constants::DAYS_AFTER_FIRST_INVITE_TO_SEND_FIRST_REMINDER);
//        Carbon::setTestNow($firstReminderSentAt);
//        Mail::fake();
//        Twilio::fake();
//        RemindEnrollees::dispatchNow($initialInviteSentAt, $enrollee1->practice_id);


    }

    private function setFakeNotification(int $patientId, int $enrolleeId, string $time, $isReminder = true)
    {
        \DB::table('notifications')->insert([
            'id'              => Str::uuid(),
            'notifiable_type' => User::class,
            'notifiable_id'   => $patientId,
            'type'            => SelfEnrollmentInviteNotification::class,
            'data'            => json_encode([
                'enrollee_id'    => $enrolleeId,
                'is_reminder'    => $isReminder,
                'is_survey_only' => true,
            ]),
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }
}
