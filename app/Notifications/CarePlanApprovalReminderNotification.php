<?php

namespace App\Notifications;

use App\Mail\CarePlanApprovalReminder;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CarePlanApprovalReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $recipient;

    protected $numberOfCareplans;




    public function __construct(User $recipient, $numberOfCareplans = null)
    {
        $this->recipient = $recipient;

        $this->numberOfCareplans = $numberOfCareplans;
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
     * @return CarePlanApprovalReminder
     */
    public function toMail($notifiable)
    {

        return (new CarePlanApprovalReminder($this->recipient, $this->numberOfCareplans));

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

        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'numberOfCareplans' => $this->numberOfCareplans
        ];
    }
}
