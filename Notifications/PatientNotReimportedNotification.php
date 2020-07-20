<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientNotReimportedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use Queueable;
    /**
     * @var int
     */
    public $patientId;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * {@inheritdoc}
     */
    public function description($notifiable): string
    {
        return "If you have the patient's MRN number number, please enter on the patient profile and attempt reimporting.";
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($notifiable): string
    {
        return "Sorry, Patient ID $this->patientId could not be reimported";
    }

    /**
     * {@inheritdoc}
     */
    public function redirectLink($notifiable): string
    {
        return route('patient.summary', [$this->patientId]);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray($notifiable): array
    {
        return $this->notificationData($notifiable);
    }

    /**
     * {@inheritdoc}
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([]);
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
            ->subject($this->getSubject($notifiable))
            ->line($this->description($notifiable))
            ->action('Go to Patient Overview', $this->redirectLink($notifiable))
            ->line('Thank you for using our application!');
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
        return ['database'];
    }
}
