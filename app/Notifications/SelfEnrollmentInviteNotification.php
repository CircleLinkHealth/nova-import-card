<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Notifications\Channels\AutoEnrollmentMailChannel;
use App\Notifications\Channels\CustomTwilioChannel;

use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Spatie\RateLimitedMiddleware\RateLimited;

class SelfEnrollmentInviteNotification extends Notification
{
    
    use EnrollableNotificationContent;
    use Queueable;
    /**
     * @var array|string[]
     */
    private $channels;

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
    public function __construct(string $url, bool $isReminder = false, array $channels = ['mail', CustomTwilioChannel::class])
    {
        $this->isReminder = $isReminder;
        $this->url        = $url;
        $this->channels   = $channels;
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
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($notifiable->isSurveyOnly()) {
            $enrollee = Enrollee::whereUserId($notifiable->id)->first();

            if ( ! $enrollee) {
                throw new \Exception("Could not find enrollee for user[$notifiable->id]");
            }

            return [
                'enrollee_id'    => $enrollee->id,
                'is_reminder'    => $this->isReminder,
                'is_survey_only' => true,
            ];
        }

        return [
            'is_reminder'    => $this->isReminder,
            'is_survey_only' => false,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);

        $fromName = config('mail.from.name'); //@todo: We dont need to show CircleLinkHealth as default
        if ( ! empty($notifiable->primaryPractice) && ! empty($notifiable->primaryPractice->display_name)) {
            $fromName = $notifiable->primaryPractice->display_name;
        }

        if (empty($this->url)) {
            throw new InvalidArgumentException("`url` cannot be empty. User ID {$notifiable->id}");
        }

        return (new AutoEnrollmentMailChannel($fromName))
            ->from(config('mail.from.address'), $fromName)
            ->subject('Wellness Program')
            ->line($notificationContent['line1'])
            ->line($notificationContent['line2'])
            ->action('Get my Care Coach', $this->url);
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
        return array_merge(['database'], $this->channels);
    }
}
