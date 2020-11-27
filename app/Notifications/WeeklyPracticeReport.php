<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyPracticeReport extends Notification
{
    use Queueable;

    protected $data;

    protected $subject;

    /**
     * Create a new notification instance.
     *
     * @param mixed $subject
     */
    public function __construct(array $data, $subject)
    {
        $this->data    = $data;
        $this->subject = $subject;
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

    public function toDatabase($notifiable)
    {
        return
            [
                'data' => $this->data,
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
        $saasAccountName = is_object($notifiable) && method_exists($notifiable, 'saasAccountName')
            ? $notifiable->saasAccountName()
            : 'CircleLink Health';

        return (new MailMessage())->view('cpm-admin::sales.by-practice.report', ['data' => $this->data])
            ->from('notifications@careplanmanager.com', $saasAccountName)
            ->subject($this->subject);
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
        if (isset($notifiable->id)) {
            return [
                'mail',
                'database',
            ];
        }

        return [
            'mail',
        ];
    }
}
