<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Notifications\Channels\CustomTwilioChannel;
use App\Traits\EnrollableManagement;
use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Spatie\RateLimitedMiddleware\RateLimited;

class SendEnrollementSms extends Notification implements ShouldQueue
{
    use EnrollableManagement;
    use EnrollableNotificationContent;
    use Queueable;

    /**
     * @var bool
     */
    private $isReminder;
    /**
     * @var string
     */
    private $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $url, bool $isReminder = false)
    {
        $this->isReminder = $isReminder;
        $this->url        = $url;
    }

    public function middleware()
    {
        $rateLimitedMiddleware = (new RateLimited())
            ->allow(10)
            ->everySeconds(60)
            ->releaseAfterSeconds(90);

        return [$rateLimitedMiddleware];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
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

        if (empty($this->url)) {
            throw new InvalidArgumentException("`url` cannot be empty. User ID {$notifiable->id}");
        }

        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);
        $smsSubject          = $notificationContent['line1'].$notificationContent['line2'].$this->url;

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
        return ['database', CustomTwilioChannel::class];
    }
}
