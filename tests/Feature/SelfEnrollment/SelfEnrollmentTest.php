<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\SelfEnrollment;

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Jobs\LogSuccessfulLoginToDB;
use App\LoginLogout;
use App\SelfEnrollment\Constants;
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
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Notification;
use PrepareDataForReEnrollmentTestSeeder;
use Tests\Concerns\TwilioFake\Twilio;

class SelfEnrollmentTest extends TestCase
{
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
            'id'              => Str::uuid(),
            'notifiable_type' => User::class,
            'notifiable_id'   => $patient->id,
            'type'            => SelfEnrollmentInviteNotification::class,
            'data'            => json_encode([
                'enrollee_id'    => $enrollee->id,
                'is_reminder'    => true,
                'is_survey_only' => true,
            ]),
            'created_at' => now()->subMonth()->toDateTimeString(),
            'updated_at' => now()->subMonth()->toDateTimeString(),
        ]);
        $invitationBatch = EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        );
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

        SendInvitation::dispatchNow($patient, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);
        self::assertTrue(User::hasSelfEnrollmentInvite()->where('id', $patient->id)->exists());
        //It should not show up on the list on the "needs reminder" list of patients we invited yesterday
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now()->subDay())->where('id', $patient->id)->exists());

        //It should show up on the list on the "needs reminder" list of patients we invited today
        self::assertTrue(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());
        SendReminder::dispatchNow($patient);
        //It should not show up because we just sent a reminder
        self::assertFalse(User::haveEnrollableInvitationDontHaveReminder(now())->where('id', $patient->id)->exists());

        //SendReminder should be allowed to run one more time to send a second reminder
        self::assertTrue(with(new SendReminder($patient))->shouldRun());
    }

    public function test_it_removes_email_channel_if_fake_email()
    {
        $enrollee = $this->createEnrollees();

        $enrollee->user->email = 'test@careplanmanager.com';
        $enrollee->user->save();

        $this->assertFalse(in_array('mail', (new SelfEnrollmentInviteNotification('hello'))->via($enrollee->user)));
    }

    public function test_it_saves_different_enrollment_link_in_db_when_sending_reminder()
    {
        $enrollee = $this->createEnrollees($number = 1);
        $patient  = $enrollee->user;

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
            $color = SelfEnrollmentController::RED_BUTTON_COLOR,
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
            SendInvitation::dispatchNow($enrollee->user, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
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
            SendInvitation::dispatchNow($enrollee->user, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
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

    public function test_patient_has_clicked_get_my_care_coach()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_PENDING);
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
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_COMPLETED);
        self::assertTrue(SelfEnrollmentController::ENROLLMENT_SURVEY_COMPLETED === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_survey_in_progress()
    {
        $enrollee       = $this->createEnrollees(1);
        $patient        = $enrollee->user;
        $surveyInstance = $this->createSurveyConditionsAndGetSurveyInstance($patient->id, SelfEnrollmentController::ENROLLMENT_SURVEY_IN_PROGRESS);
        self::assertTrue(SelfEnrollmentController::ENROLLMENT_SURVEY_IN_PROGRESS === Helpers::awvUserSurveyQuery($patient, $surveyInstance)->first()->status);
    }

    public function test_patient_has_viewed_login_form()
    {
        $enrollee = $this->createEnrollees(1);
        $patient  = $enrollee->user;
        Notification::fake();
        Mail::fake();
        SendInvitation::dispatch($patient, EnrollmentInvitationsBatch::firstOrCreateAndRemember(
            $enrollee->practice_id,
            now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.EnrollmentInvitationsBatch::MANUAL_INVITES_BATCH_TYPE
        )->id);
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
}
