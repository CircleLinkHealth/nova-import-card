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
            'channels'          => $this->channels,
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
        return View::make(
            'emails.DmCareplanApprovalReminder',
            [
                'notifiable'        => $notifiable,
                'numberOfCareplans' => $this->numberOfCareplans,
            ]
        );
    }
    
    
    /**
     * Get the notification's delivery channels.
     *
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        $channels = collect([]);
        
        $settings = $notifiable->practiceSettings();
        
        if ($settings->email_careplan_approval_reminders) {
            $channels->push(MailChannel::class);
        }
        if ($settings->dm_careplan_approval_reminders) {
            $channels->push(DirectMailChannel::class);
        }
        
        $this->channels = array_merge(
            $this->channels,
            $channels->toArray()
        );
        
        return $this->channels;
    }
}
