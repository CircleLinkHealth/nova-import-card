<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\Traits\ArrayableNotification;
use Illuminate\Notifications\Messages\BroadcastMessage;

interface LiveNotification
{
    /**
     * A string with the attachments name. eg. "Addendum".
     *
     * @param mixed $notifiable
     */
    public function description($notifiable): string;

    /**
     * A sentence to present the notification.
     *
     * @param mixed $notifiable
     */
    public function getSubject($notifiable): string;

    /**
     * Array representation of required live notification data.
     * Using ArrayableNotification trait covers everything needed.
     *
     * @see ArrayableNotification
     *
     * @param $notifiable
     */
    public function notificationData($notifiable): array;

    /**
     * Redirect link to activity.
     *
     * @param mixed $notifiable
     */
    public function redirectLink($notifiable): string;

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array;

    /**
     * Get the broadcastable representation of the notification.
     *
     * NOTE: The `notification_id` and `notification_type` are automatically included by default.
     *
     * @param mixed $notifiable
     */
    public function toBroadcast($notifiable): BroadcastMessage;
}
