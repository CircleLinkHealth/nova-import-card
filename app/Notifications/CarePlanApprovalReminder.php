<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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
     */
    protected $numberOfCareplans;

    public function __construct($numberOfCareplans)
    {
        $this->numberOfCareplans = $numberOfCareplans;
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
        return [
            'numberOfCareplans' => $this->numberOfCareplans,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param User $notifiable
     *
     * @return CarePlanApprovalReminderMailable
     */
    public function toMail(User $notifiable)
    {
        return new CarePlanApprovalReminderMailable($notifiable, $this->numberOfCareplans);
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
