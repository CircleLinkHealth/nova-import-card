<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\DirectMailableNotification;
use App\Notifications\Channels\DirectMailChannel;
use App\ValueObjects\SimpleNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CarePlanDMApprovalConfirmation extends Notification implements DirectMailableNotification, ShouldQueue
{
    use Queueable;
    /**
     * @var User
     */
    protected $patientUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
    }

    /**
     * {@inheritdoc}
     */
    public function directMailBody($notifiable): string
    {
        return "Thanks for approving {$this->patientUser->getFullName()}}'s Care Plan! Have a great day - CircleLink Team";
    }

    /**
     * {@inheritdoc}
     */
    public function directMailSubject($notifiable): string
    {
        return 'Care Plan Approved';
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
            'body'       => $this->directMailBody($notifiable),
            'subject'    => $this->directMailSubject($notifiable),
            'patient_id' => $this->patientUser->id,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toDirectMail($notifiable): SimpleNotification
    {
        return (new SimpleNotification())
            ->setSubject($this->directMailSubject($notifiable))
            ->setBody($this->directMailBody($notifiable))
            ->setPatient($this->patientUser);
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
        return ['database', DirectMailChannel::class];
    }
}
