<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Traits\EnrollableManagement;
use App\Traits\EnrollableNotificationContent;
use App\Traits\HasEnrollableInvitation;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SendEnrollementSms extends Notification
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
     * @return TwilioSmsMessage
     */
    public function toTwilio(User $notifiable)
    {
//        at this point will always exist only one active link from the mail notif send
        $receiver = $this->getEnrollableModelType($notifiable);
//        $practiceNumber = $receiver->primaryPractice->outgoing_phone_number;
        $invitationUrl = $receiver->getLastEnrollmentInvitationLink();
        $shortenUrl    = null;

        try {
            $shortenUrl = shortenUrl($invitationUrl->url);
        } catch (\Exception $e) {
            \Log::warning($e->getMessage());
        }

        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);
        $smsSubject          = $notificationContent['line1'].$notificationContent['line2'].$shortenUrl ?? $invitationUrl->url;

//        return (new TwilioSmsMessage())
//            ->from($practiceNumber)
//            ->content($smsSubject);
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
