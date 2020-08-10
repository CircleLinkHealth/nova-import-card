<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EligibilityBatchProcessed extends Notification implements LiveNotification
{
    use ArrayableNotification;
    use Queueable;
    /**
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * Create a new notification instance.
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * {@inheritdoc}
     */
    public function description($notifiable): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($notifiable): string
    {
        return "Eligibility Batch {$this->batch->id} has finished processing";
    }

    /**
     * {@inheritdoc}
     */
    public function redirectLink($notifiable): string
    {
        return route('eligibility.batch.show', [$this->batch->id]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
        ];
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
            ->line('Click link below to Download Eligible Patients CSV and schedule calls')
            ->action('View Batch', $this->redirectLink($notifiable))
            ->line('Thank you for using our CarePlan Manager!');
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
        return ['database', 'mail'];
    }
}
