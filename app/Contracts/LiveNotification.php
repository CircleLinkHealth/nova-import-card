<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface LiveNotification
{
    //Gets the notification attachment type. eg. App\Models\Addendum
    public function attachmentType(): string;

    //A string with the attachments name. eg. "Addendum"
    public function description(): string;

    // Attachments model
    public function getAttachment();

    public function getPatientName(): string;

    //A sentence to present the notification. eg. Johny "responded to a note on" Bob
    public function getSubject(): string;

    public function noteId(): ?int;

    //On live notification click redirect user to view the call/addendum etc.
    public function redirectLink(): string;

    //User id who sends the notification
    public function senderId(): int;

    public function senderName(): string;
    //Json array. Keeps all data needed to represent notification in vue
    public function toArray($notifiable): array;
    //Returns by default -  ONLY the notification id & the notification type to be used in broadcasting the notification
    //Broadcast will be listened by BroadcastServiceProvider
    public function toBroadcast($notifiable): object;
}
