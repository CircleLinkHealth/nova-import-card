<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\SelfEnrollment\Notifications;

use CircleLinkHealth\Eligibility\SelfEnrollment\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
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
    public function __construct(string $url, bool $isReminder = false, array $channels = ['mail', 'twilio'])
    {
        $this->isReminder = $isReminder;
        $this->url        = $url;
        $this->channels   = $channels;
    }

    public function middleware()
    {
        $rateLimitedMiddleware = (new RateLimited())
            ->allow(300)
            ->everySeconds(60)
            ->releaseAfterSeconds(90);

        return [$rateLimitedMiddleware];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * @param mixed $notifiable
     *
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

        $fromName = null;

        if ( ! empty($notifiable->primaryPractice) && ! empty($notifiable->primaryPractice->display_name)) {
            $fromName = $notifiable->primaryPractice->display_name;
        }

        if (empty($fromName)) {
            $fromName = config('mail.marketing_from.name');
        }

        if (empty($this->url)) {
            throw new InvalidArgumentException("`url` cannot be empty. User ID {$notifiable->id}");
        }

        $mailMessage           = new MailMessage();
        $mailMessage->viewData = ['excludeLogo' => true, 'practiceName' => $fromName];

        return $mailMessage
            ->mailer('smtp')
            ->from(config('mail.marketing_from.address'), $fromName)
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
     *
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
        if (in_array('mail', $this->channels)
            && ! App::environment(['local', 'staging', 'review'])
            && (
                Str::contains($notifiable->email, ['@careplanmanager.com', '@example.com', '@noEmail.com'])
            || empty($notifiable->email)
            )) {
            unset($this->channels[array_search('mail', $this->channels)]);
        }

        return array_merge(['database'], $this->channels);
    }
}
