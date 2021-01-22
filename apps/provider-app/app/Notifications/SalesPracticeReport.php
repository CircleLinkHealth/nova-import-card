<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\CpmAdmin\Mail\SalesPracticeReport as SalesPracticeReportMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SalesPracticeReport extends Notification
{
    use Queueable;

    protected $data;

    protected $recipientEmail;

    /**
     * Create a new notification instance.
     *
     * @param mixed $recipientEmail
     */
    public function __construct(array $data, $recipientEmail)
    {
        $this->data           = $data;
        $this->recipientEmail = $recipientEmail;
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
        return
            [
                'data' => $this->data,
            ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return SalesPracticeReportMailable
     */
    public function toMail($notifiable)
    {
        return new SalesPracticeReportMailable($notifiable, $this->data, $this->recipientEmail);
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
        return [
            'mail',
            'database',
        ];
    }
}
