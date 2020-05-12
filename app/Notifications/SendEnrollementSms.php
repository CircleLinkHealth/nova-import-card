<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Traits\EnrollableManagement;
use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\HasEnrollableInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SendEnrollementSms extends Notification implements ShouldQueue
{
    use EnrollableManagement;
    use EnrollableNotificationContent;
    use HasEnrollableInvitation;
    use Queueable;

    /**
     * @var bool
     */
    private $isReminder;

    /**
     * Create a new notification instance.
     *
     * @param bool $isReminder
     */
    public function __construct($isReminder = false)
    {
        $this->isReminder = $isReminder;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * The phone number to send text is in Notifiable Model->routeNotificationForTwilio().
     *
     * @param $notifiable
     *
     * @throws \Exception
     * @return TwilioSmsMessage
     */
    public function toTwilio(User $notifiable)
    {
        // at this point will always exist only one active link from the mail notif send
        $receiver = $this->getEnrollableModelType($notifiable);
        if ( ! $receiver) {
            $hasSurveyRole = $notifiable->isSurveyOnly();
            throw new \Exception("Could not deduce user[$notifiable->id] to a receiver. User is survey-role only: $hasSurveyRole");
        }

        $invitationUrl = $receiver->getLastEnrollmentInvitationLink();
        $shortenUrl    = $invitationUrl->url;

        try {
            $shortenUrl = shortenUrl($invitationUrl->url);
        } catch (\Exception $e) {
            \Log::warning($e->getMessage());
        }

        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);
        $smsSubject          = $notificationContent['line1'].$notificationContent['line2'].$shortenUrl;

        return (new TwilioSmsMessage())
//            ->from($practiceNumber)
            ->content($smsSubject);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', TwilioChannel::class];
    }
}
