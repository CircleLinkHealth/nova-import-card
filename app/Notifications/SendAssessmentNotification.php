<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\SharedModels\Entities\CareplanAssessment;
use CircleLinkHealth\Core\Contracts\FaxableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendAssessmentNotification extends Notification implements FaxableNotification
{
    //todo: REMOVE PHI FROM NOTIFICATION
    use Queueable;

    public $pathToPdf;
    private $approver;

    private $attachment;

    //Letting this go to just database so we can see if this actually is being used by anyone.
    //If we need this we can retrieve for database
    private $channels = ['database'];
    private $patient;
    private $practice;

    /**
     * Create a new notification instance.
     */
    public function __construct(CareplanAssessment $assessment)
    {
        $this->attachment = $assessment;
        $this->patient    = $assessment->patient()->first();
        $this->approver   = $assessment->approver()->first();
        if ($this->approver) {
            $this->practice = $this->approver->practices()->first();
        }
    }

    public function getAttachment()
    {
        return $this->attachment;
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
            'channels' => $this->channels,

            'receiver_type'  => $notifiable->id,
            'receiver_id'    => get_class($notifiable),
            'receiver_email' => $notifiable->email ?? $notifiable->routeNotificationForMail(),

            'pathToPdf'  => $this->toPdf($notifiable),
            'assessment' => $this->attachment,
        ];
    }

    /**
     * @param $notifiable
     */
    public function toFax($notifiable = null): array
    {
        return [
            'file' => $this->toPdf($notifiable),
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject('New Patient Assessment')
            ->view(
                'emails.assessment-created',
                [
                    'assessment' => $this->attachment,
                    'notifiable' => $notifiable,
                ]
            );
    }

    public function toPdf($notifiable = null)
    {
        if ( ! file_exists($this->pathToPdf)) {
            $this->pathToPdf = $this->attachment->toPdf($notifiable);
        }

        return $this->pathToPdf;
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
        return $this->channels;
    }
}
