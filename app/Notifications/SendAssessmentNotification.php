<?php

namespace App\Notifications;

use App\CareplanAssessment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendAssessmentNotification extends Notification
{
    use Queueable;

    private $attachment;
    private $patient;
    private $approver;
    private $practice;
    private $channels = ['mail', 'database'];
    public $pathToPdf;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(CareplanAssessment $assessment)
    {
        $this->attachment = $assessment;
        $this->patient = $assessment->patient()->first();
        $this->approver = $assessment->approver()->first();
        if ($this->approver) {
            $this->practice = $this->approver->practices()->first();
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->from('notifications@careplanmanager.com', 'CircleLink Health')
                    ->subject('New Patient Assessment')
                    ->view('emails.assessment-created', [
                        'assessment' => $this->attachment,
                        'notifiable' => $notifiable
                    ]);
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
            'channels' => $this->channels,
            'sender_id'    => auth()->user()->id,
            'sender_type'  => SendAssessmentNotification::class,
            'sender_email' => auth()->user()->email,

            'receiver_type'  => $notifiable->id,
            'receiver_id'    => get_class($notifiable),
            'receiver_email' => $notifiable->email ?? $notifiable->routeNotificationForMail(),

            'pathToPdf' => $this->toPdf($notifiable),
            'assessment' => $this->attachment
        ];
    }

    public function toFax($notifiable)
    {
        if (! $notifiable || ! $notifiable->fax) {
            return false;
        }
        return $this->toPdf($notifiable);
    }

    public function toPdf($notifiable = null)
    {
        if (! file_exists($this->pathToPdf)) {
            $this->pathToPdf = $this->attachment->toPdf($notifiable);
        }
        return $this->pathToPdf;
    }
    
    public function getAttachment()
    {
        return $this->attachment;
    }
}
