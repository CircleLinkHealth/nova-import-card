<?php

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
     * @var mixed
     */
    private $modelName;
    /**
     * @var mixed
     */
    private $receiver;

    /**
     * Create a new notification instance.
     *
     * @param $receiver
     * @param bool $isReminder
     */
    public function __construct(User $receiver, $isReminder = false)
    {
        $this->receiver = $receiver;
        $this->isReminder = $isReminder;
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
        $invitationUrl = $notifiable->getLastEnrollmentInvitationLink();

        if (empty($invitationUrl)) {
            $urlData = [
                'enrollable_id' => $notifiable->id,
                'is_survey_only' => $notifiable->checkForSurveyOnlyRole(),
            ];
            $invitationUrl = $this->createInvitationLink($notifiable, $urlData);
        }

        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);
        $smsSubject = $notificationContent['line1'] . $notificationContent['line2'] . $invitationUrl->url;
        return (new TwilioSmsMessage())
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
        return [TwilioChannel::class];
    }
}
