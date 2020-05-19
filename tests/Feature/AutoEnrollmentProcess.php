<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\CreateUsersFromEnrollees;
use App\Jobs\EnrollmentSeletiveInviteEnrollees;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use App\Jobs\SendEnrollmentReminders;
use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use NotificationChannels\Twilio\TwilioChannel;
use Tests\CustomerTestCase;

class AutoEnrollmentProcess extends CustomerTestCase
{
    use EnrollableManagement;
    /**
     * @var
     */
    protected $enrollee;

    public function check_notification_mail_has_been_sent($user)
    {
        Notification::assertSentTo(
            $user,
            SendEnrollmentEmail::class,
            function (SendEnrollmentEmail $notification, $channels, $notifiable) use ($user) {
                $notification->createInvitationLink($notifiable);
                self::assertEquals(['database', 'mail'], $channels);

                return (int) $notifiable->id === (int) $user->id;
            }
        );
    }

//
//    public function check_notification_sms_has_been_sent($enrollable)
//    {
//        Notification::assertSentTo(
//            $enrollable,
//            SendEnrollementSms::class,
//            function (SendEnrollementSms $notification, $channels, $notifiable) use ($enrollable) {
//                self::assertEquals(['database', TwilioChannel::class], $channels);
//
//                return (int) $notifiable->id === (int) $enrollable->id;
//            }
//        );
//    }
//
    public function surveyRole()
    {
        return Role::firstOrCreate(
            [
                'name' => 'survey-only',
            ],
            [
                'display_name' => 'Survey User',
                'description'  => 'Became Users just to be enrolled in AWV surveyRole',
            ]
        );
    }

    public function test_it_creates_user_from_enrollee()
    {
        $enrollee = $this->enrollee();
        CreateUsersFromEnrollees::dispatch([$enrollee->id]);
        self::assertTrue( ! is_null($enrollee->fresh()->user_id));
    }

    public function test_it_sends_invitations_to_enrollee()
    {
        Notification::fake();
        //        See. EnrolleeObserver
        $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
            ->createEnrollee($this->practice());
        EnrollmentSeletiveInviteEnrollees::dispatch([$enrollee->fresh()->user_id]);
        $this->check_notification_mail_has_been_sent($enrollee->fresh()->user);
//        $this->check_notification_sms_has_been_sent($enrollle->fresh()->user);

        self::assertTrue($enrollee->enrollmentInvitationLink()->exists());
        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($enrollee),
            'invitationable_id'   => $enrollee->id,
            'manually_expired'    => false,
        ]);
    }

//    public function test_it_sends_just_one_reminder_to_enrollee()
//    {
//
//    }

//
//    public function test_it_sends_invitations_to_unreachable_patient()
//    {
//        $patient = $this->patient();
//        Notification::fake();
//        SelfEnrollmentUnreachablePatients::dispatchNow($patient, 1, $this->demoPractice()->id);
//        $this->check_notification_mail_has_been_sent($patient);
    ////        $this->check_notification_sms_has_been_sent($patient);
//
//        self::assertTrue($this->patient()->enrollmentInvitationLink()->exists());
//        $this->assertDatabaseHas('enrollables_invitation_links', [
//            'invitationable_type' => get_class($patient),
//            'invitationable_id'   => $patient->id,
//            'manually_expired'    => false,
//        ]);
//    }
//
    public function test_it_sends_reminder_to_enrollee()
    {
        $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
            ->createEnrollee($this->practice());
        $patient = $enrollee->fresh()->user;

        Notification::fake();
        SendEnrollmentReminders::dispatchNow($patient);

        $this->check_notification_mail_has_been_sent($patient);
//        $this->check_notification_sms_has_been_sent($patient);

        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($enrollee),
            'invitationable_id'   => $enrollee->id,
            'manually_expired'    => false,
        ]);

        self::assertTrue($enrollee->enrollmentInvitationLink()->exists());
    }

    public function test_patient_has_logged_in()
    {
//         $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
//            ->createEnrollee($this->practice());
//        Auth::loginUsingId($enrollee->fresh()->user_id, true);
//        self::assertDatabaseHas('login_logout_events', [
//            'user_id' => $enrollee->fresh()->user_id,
//        ]);
    }

    public function test_patient_has_survey_in_progress()
    {
//        $userId = 666;
//
//        $surveyId = DB::table('surveys')->insertGetId([
//            'name' => 'Enrollees',
//        ]);
//
//        $surveyInstanceId = DB::table('survey_instances')->insertGetId([
//            'survey_id' => $surveyId,
//            'year'      => Carbon::now(),
//        ]);
//
//        DB::table('users_survey')->insert(
//            [
//                'user_id'            => $userId,
//                'survey_instance_id' => $surveyInstanceId,
//                'survey_id'          => $surveyId,
//                'status'             => 'in_progress',
//            ]
//        );
//        $surveyInstance = DB::table('survey_instances')->where('id', '=', $surveyInstanceId)->first();
//        self::assertTrue('in_progress' === $this->getAwvUserSurvey($userId, $surveyInstance)->first()->status);
    }

//
//    public function test_it_sends_reminder_to_non_responding_patient()
//    {
//        $patient = $this->patient();
//        Notification::fake();
//        SendEnrollmentReminders::dispatchNow($patient);
//
//        $this->check_notification_mail_has_been_sent($patient);
    ////        $this->check_notification_sms_has_been_sent($patient);
//
//        $this->assertDatabaseHas('enrollables_invitation_links', [
//            'invitationable_type' => get_class($patient),
//            'invitationable_id'   => $patient->id,
//            'manually_expired'    => false,
//        ]);
//
//        self::assertTrue($patient->enrollmentInvitationLink()->exists());
//    }

    public function test_patient_has_viewed_login_form()
    {
        $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
            ->createEnrollee($this->practice());
        EnrollmentSeletiveInviteEnrollees::dispatch([$enrollee->fresh()->user_id]);
        $enrollee->enrollmentInvitationLink->manually_expired = true;
        $enrollee->enrollmentInvitationLink->save();
//    If patient has link expired = has opened the link and seen the login form.
        self::assertTrue(optional($enrollee->enrollmentInvitationLink())->where('manually_expired', true)->exists());
    }

//    Meaning they will get physical mail.
//    public function test_only_patients_taken_no_action_will_be_marked_as_unresponsive()
//    {
//    }

// test authentication

// test reminder _ only setnd just one remidner.
}
