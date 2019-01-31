<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Mail\CarePlanApprovalReminder as CarePlanApprovalReminderMailable;
use App\Notifications\Channels\DirectMailChannel;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;

class CarePlanApprovalReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $numberOfCareplans;

    public $channels = ['database'];

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
     * @param User $notifiable
     *
     * @return array|bool
     * @throws \Exception
     */
    public function toDirectMail(User $notifiable)
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return [
            'body'    => $this->directMailBody($notifiable),
            'subject' => "{$this->numberOfCareplans} CircleLink Care Plan(s) for your Approval!",
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function directMailBody(User $notifiable)
    {
        return View::make('emails.DmCareplanApprovalReminder', [
            'notifiable'        => $notifiable,
            'numberOfCareplans' => $this->numberOfCareplans,
        ]);
    }


    /**
     * Get the notification's delivery channels.
     *
     * The logic here is:
     * If notifiable has email reminders enabled OR DIRECT reminders but NOT a direct address, we sent to mail.
     * If notifiable has DIRECT reminders AND address we sent via DIRECT.
     * If notifiable has BOTH email and direct reminders enabled AND a direct address, we sent both.
     *
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {

        $channels = collect([]);

        if ($notifiable->practiceSettings()->email_careplan_approval_reminders) {
            $channels->push(MailChannel::class);
        }
        $channels->push($notifiable->practiceSettings()->dm_careplan_approval_reminders && $notifiable->emr_direct_address
            ? DirectMailChannel::class
            : MailChannel::class);

        $this->channels = array_merge(
            $this->channels,
            $channels->unique()->toArray()
        );

        return $this->channels;
    }
}
