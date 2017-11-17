<?php

namespace App\Notifications;

use App\Mail\CarePlanApprovalReminder as CarePlanApprovalReminderMailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CarePlanApprovalReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $numberOfCareplans;


    public function __construct($numberOfCareplans = null)
    {
        $this->numberOfCareplans = $numberOfCareplans;
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
        return [
            'mail',
            'database',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  User $notifiable
     *
     * @return CarePlanApprovalReminderMailable
     */
    public function toMail(User $notifiable)
    {
        return new CarePlanApprovalReminderMailable($notifiable, $this->numberOfCareplans);
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

        ];
    }

    public function toDatabase($notifiable)
    {
        return [
            'numberOfCareplans' => $this->numberOfCareplans,
        ];
    }
}
