<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\CreateUserFromEnrollee;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use App\Jobs\SendEnrollmentReminders;
use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use CircleLinkHealth\Core\Facades\Notification;
use CircleLinkHealth\Customer\Entities\Role;
use NotificationChannels\Twilio\TwilioChannel;
use Tests\CustomerTestCase;

class AutoEnrollmentProcess extends CustomerTestCase
{
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

    public function check_notification_sms_has_been_sent($enrollable)
    {
        Notification::assertSentTo(
            $enrollable,
            SendEnrollementSms::class,
            function (SendEnrollementSms $notification, $channels, $notifiable) use ($enrollable) {
                self::assertEquals(['database', TwilioChannel::class], $channels);

                return (int) $notifiable->id === (int) $enrollable->id;
            }
        );
    }

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

    public function test_it_sends_invitations_to_enrollee()
    {
        Notification::fake();
        $enrollle = $this->enrollee();

        CreateUserFromEnrollee::dispatchNow($enrollle, $this->surveyRole()->id);
        $this->check_notification_mail_has_been_sent($enrollle->fresh()->user);
        $this->check_notification_sms_has_been_sent($enrollle->fresh()->user);

        self::assertTrue($enrollle->enrollmentInvitationLink()->exists());
        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($enrollle),
            'invitationable_id'   => $enrollle->id,
            'manually_expired'    => false,
        ]);
    }

    public function test_it_sends_invitations_to_unreachable_patient()
    {
        $patient = $this->patient();
        Notification::fake();
        SelfEnrollmentUnreachablePatients::dispatchNow($patient);
        $this->check_notification_mail_has_been_sent($patient);
//        $this->check_notification_sms_has_been_sent($patient);

        self::assertTrue($this->patient()->enrollmentInvitationLink()->exists());
        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($patient),
            'invitationable_id'   => $patient->id,
            'manually_expired'    => false,
        ]);
    }

    public function test_it_sends_reminder_to_non_responding_enrollee()
    {
        $enrollee = $this->enrollee();
        $patient  = $this->patient();
        $patient->attachGlobalRole($this->surveyRole()->id);
        $enrollee->update(['user_id' => $patient->id]);

        Notification::fake();
        SendEnrollmentReminders::dispatchNow($patient);

        $this->check_notification_mail_has_been_sent($patient);
        $this->check_notification_sms_has_been_sent($patient);

        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($enrollee),
            'invitationable_id'   => $enrollee->id,
            'manually_expired'    => false,
        ]);

        self::assertTrue($enrollee->enrollmentInvitationLink()->exists());
    }

    public function test_it_sends_reminder_to_non_responding_patient()
    {
        $patient = $this->patient();
        Notification::fake();
        SendEnrollmentReminders::dispatchNow($patient);

        $this->check_notification_mail_has_been_sent($patient);
        $this->check_notification_sms_has_been_sent($patient);

        $this->assertDatabaseHas('enrollables_invitation_links', [
            'invitationable_type' => get_class($patient),
            'invitationable_id'   => $patient->id,
            'manually_expired'    => false,
        ]);

        self::assertTrue($patient->enrollmentInvitationLink()->exists());
    }

//    public function test_patient_logins_before_redirect()
//    {
//    }

//    Meaning they will get physical mail.
//    public function test_only_patients_taken_no_action_will_be_marked_as_unresponsive()
//    {
//    }
}
