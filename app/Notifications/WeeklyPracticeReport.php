<?php

namespace App\Notifications;

use App\Mail\WeeklyPracticeReport as WeeklyPracticeReportMailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
        $this->data = $data;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
        'mail',
        'database',
    ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return WeeklyPracticeReportMailable
     */
    public function toMail(User $notifiable)
    {
        return new WeeklyPracticeReportMailable($notifiable, $this->data, $this->subject);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
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
