<?php

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
     * @return void
     */
    public function __construct(array $data, $subject)
    {
        $this->data    = $data;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->view('sales.by-practice.report', ['data' => $this->data])
                                ->from('notifications@careplanmanager.com',
                                    optional($notifiable)->saasAccountName() ?? 'CircleLink Health')
                                ->subject($this->subject);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        return
            [
                'data' => $this->data,
            ];
    }
}
