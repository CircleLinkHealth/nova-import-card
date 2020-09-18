<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Core\Contracts\DirectMailableNotification;
use App\Mail\CarePlanApprovalReminder as CarePlanApprovalReminderMailable;
use CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel;
use CircleLinkHealth\Core\DTO\SimpleNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;

class CarePlanApprovalReminder extends Notification implements ShouldQueue, DirectMailableNotification
{
    use Queueable;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new notification instance.
     */
    protected $numberOfCareplans;

    public function __construct($numberOfCareplans)
    {
        $this->numberOfCareplans = $numberOfCareplans;
    }

    /**
     * @param null $notifiable
     */
    public function directMailBody($notifiable): string
    {
        return View::make(
            'emails.DmCareplanApprovalReminder',
            [
                'notifiable'        => $notifiable,
                'numberOfCareplans' => $this->numberOfCareplans,
            ]
        );
    }

    public function directMailSubject($notifiable): string
    {
        return "{$this->numberOfCareplans} CircleLink Care Plan(s) for your Approval!";
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
            'channels'          => $this->via($notifiable),
            'numberOfCareplans' => $this->numberOfCareplans,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    /**
     * @param mixed $notifiable
     *
     * @throws \Exception
     *
     * @return array|bool
     */
    public function toDirectMail($notifiable): SimpleNotification
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return (new SimpleNotification())
            ->setSubject($this->directMailSubject($notifiable))
            ->setBody($this->directMailBody($notifiable));
    }

    /**
     * Get the mail representation of the notification.
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
        $channels = ['database'];
        $settings = $notifiable->practiceSettings();

        if ($settings->email_careplan_approval_reminders) {
            $channels[] = MailChannel::class;
        }

        if ($settings->dm_careplan_approval_reminders) {
            $channels[] = DirectMailChannel::class;
        }

        return $channels;
    }
}
