<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EligibilityBatchProcessed extends Notification
{
    use Queueable;
    /**
     * @var EligibilityBatch
     */
    private $batch;

    /**
     * Create a new notification instance.
     *
     * @param EligibilityBatch $batch
     */
    public function __construct(EligibilityBatch $batch)
    {
        $this->batch = $batch;
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
            ->subject("Eligibility Batch {$this->batch->id} has finished processing")
            ->line('Click link below to Download Eligible Patients CSV and schedule calls')
            ->action('View Batch', route('eligibility.batch.show'.[$this->batch->id]))
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
        return ['mail', 'database'];
    }
}
