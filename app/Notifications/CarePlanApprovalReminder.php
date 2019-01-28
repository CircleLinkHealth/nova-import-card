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
        return "Dear {$notifiable->getFullName()},
 
Thank you for using CircleLink Health for Chronic Care Management!
 
We are delighted to report {$this->numberOfCareplans} care plan(s) awaiting your approval.
 
To review and approve, simply copy and paste www.careplanmanager.com into a web browser and login.
 
Then, on the homepage, click \"Approve Now\" in the “Pending Care Plans” table/list (center of page), for the first patient you wish to approve.
 
You can review and approve new CCM care plans in the next page. Just click “Approve and View Next” to approve and view the next pending care plan. You can edit the care plan with green edit icons.
 
Alternatively, you can upload your own PDF care plan using the \"Upload PDF\" button. (NOTE: Please make sure uploaded PDF care plans conform to Medicare requirements.)
 
Our registered nurses will take it from here!
 
Thank you again,
CircleLink Team
 
To receive this notification less (or more) frequently, please adjust your settings by visiting this site: {$this->getManageNotificationsUrl($notifiable)}";
    }

    /**
     * @param User $notifiable
     *
     * @return string
     * @throws \Exception
     */
    private function getManageNotificationsUrl(User $notifiable)
    {
        try {
            $practice = strtolower($notifiable->primaryPractice->name);
            return "careplanmanager.com/practices/{$practice}/notifications";
        } catch (\Exception $e) {
            \Log::debug("EXCEPTION `{$e->getMessage()}`");
            throw $e;
        }
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
        array_push(
            $this->channels,
            $notifiable->primaryPractice->cpmSettings()->dm_careplan_approval_reminders && $notifiable->emr_direct_address
                ? DirectMailChannel::class
                : MailChannel::class
        );

        return $this->channels;
    }
}
