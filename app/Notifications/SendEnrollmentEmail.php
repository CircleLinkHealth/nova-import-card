<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Notifications\Channels\AutoEnrollmentMailChannel;
use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\RateLimitedMiddleware\RateLimited;

class SendEnrollmentEmail extends Notification implements ShouldQueue
{
    use EnrollableNotificationContent;
    use Queueable;

    const USER = User::class;
    /**
     * @var null
     */
    private $enrolleeModelId;
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
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray(User $notifiable)
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
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }
}
