<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use Illuminate\Notifications\Messages\BroadcastMessage;

interface LiveNotification
{//    mini documentation on LIVE NOTIFICATIONS
//    1.    Add the notification class in config/live-notifications.php
    /**
     * Gets the notification attachment type. eg. App\Models\Addendum.
     */
    public function attachmentType(): string;

    /**
     * Get the array representation of notification
     * @param $notifiable
     * @return array
     */
    public function getNotificationData($notifiable): array;

    /**
     * A string with the attachments name. eg. "Addendum".
     */
    public function description(): string;

    public function getPatientName(): string;

    /**
     * A sentence to present the notification.
     */
    public function getSubject(): string;

    public function noteId(): ?int;

    /**
     * Redirect link to activity.
     */
    public function redirectLink(): string;

    /**
     * User id who sends the notification.
     */
    public function senderId(): int;

    public function senderName(): string;

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable): array;

    /**
     * Get the broadcastable representation of the notification.
     *
     * NOTE: The `notification_id` and `notification_type` are automatically included by default.
     *
     * @param mixed $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage;
}
