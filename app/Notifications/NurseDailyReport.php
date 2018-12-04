<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Mail\NurseDailyReport as NurseDailyReportMailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NurseDailyReport extends Notification
{
    use Queueable;

    /**
     * The data passed to the view.
     *
     * For an example @see: EmailRNDailyReport, method handle
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
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
     * @param User $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(User $notifiable)
    {
        return new NurseDailyReportMailable($notifiable, $this->data);
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
        return [
            'mail',
            'database',
        ];
    }
}
