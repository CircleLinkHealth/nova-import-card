<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\SelfEnrollment\Jobs\SendReminder;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\Role;
use Illuminate\Support\Facades\Auth;
use Tests\CustomerTestCase;

class AutoEnrollmentProcessTest extends CustomerTestCase
{
    const COMPLETED = 'completed';

    const IN_PROGRESS = 'in_progress';
    const PENDING     = 'pending';

    public function check_notification_mail_has_been_sent($user)
    {
        Notification::assertSentTo(
            $user,
            SelfEnrollmentInviteNotification::class,
            function (SelfEnrollmentInviteNotification $notification, $channels, $notifiable) use ($user) {
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
//            SelfEnrollmentInviteNotification::class,
//            function (SelfEnrollmentInviteNotification $notification, $channels, $notifiable) use ($enrollable) {
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

//
//
//
//    public function test_it_sends_invitations_to_unreachable_patient()
//    {
//        $patient = $this->patient();
//        Notification::fake();
//        SendSelfEnrollmentInvitationToUnreachablePatients::dispatchNow($patient, 1, $this->demoPractice()->id);
//        $this->check_notification_mail_has_been_sent($patient);
    ////        $this->check_notification_sms_has_been_sent($patient);
//
//        self::assertTrue($this->patient()->enrollmentInvitationLinks()->exists());
//        $this->assertDatabaseHas('enrollables_invitation_links', [
//            'invitationable_type' => get_class($patient),
//            'invitationable_id'   => $patient->id,
//            'manually_expired'    => false,
//        ]);
//    }
//
    public function test_it_sends_reminder_notification_to_enrollee()
    {
        $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
            ->createEnrollee($this->practice());
        $patient = $enrollee->fresh()->user;

        Notification::fake();
        SendReminder::dispatchNow($patient);

        $this->check_notification_mail_has_been_sent($patient);
        //        $this->check_notification_sms_has_been_sent($patient);

        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($enrollee),
            'invitationable_id'   => $enrollee->id,
            'manually_expired'    => false,
        ]);

        self::assertTrue($enrollee->enrollmentInvitationLinks()->exists());
    }

    public function test_patient_has_logged_in()
    {
        $enrollee = $this->app->make(\PrepareDataForReEnrollmentTestSeeder::class)
            ->createEnrollee($this->practice());
        Auth::loginUsingId($enrollee->fresh()->user_id, true);
        self::assertDatabaseHas('login_logout_events', [
            'user_id' => $enrollee->fresh()->user_id,
        ]);
    }

//
//    public function test_it_sends_reminder_to_non_responding_patient()
//    {
//        $patient = $this->patient();
//        Notification::fake();
//        SendReminder::dispatchNow($patient);
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
//        self::assertTrue($patient->enrollmentInvitationLinks()->exists());
//    }

//    Meaning they will get physical mail.
//    public function test_only_patients_taken_no_action_will_be_marked_as_unresponsive()
//    {
//    }
}
