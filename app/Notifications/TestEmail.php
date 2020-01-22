<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Notifications\Channels\CircleLinkMailChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class TestEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function createUnsubscribeUrl($activityType)
    {
        return URL::temporarySignedRoute('unsubscribe.notifications.mail', now()->addDays(3), ['activityType' => $activityType]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
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
        $senderName       = 'Dr. Jenna Cambell';
        $wordToShowInMail = 'note';
        $lineStyled       = "<a style='color: #376a9c'> $senderName </a> has commented on a <a style='color: #376a9c'> $wordToShowInMail </a>";
        $date             = Carbon::parse(now())->toDayDateTimeString();

        $emailData = [
            'senderName'     => $senderName,
            'date'           => $date,
            'activityType'   => $wordToShowInMail,
            'notifiableMail' => $notifiable->routes['mail'],
        ];

        $unsubscribeLink = $this->createUnsubscribeUrl($emailData['activityType']);

        return (new CircleLinkMailChannel($emailData, $unsubscribeLink))
            ->line($lineStyled)
            ->action('View Comment', url('/'));
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
        return ['mail'];
    }
}