<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface PusherLiveNotifications
{
    public function description(): string;

    public function getAttachment();

    public function getPatientName(): string;

    public function getSubject(): string;

    public function redirectLink(): string;

    public function toArray($notifiable): array;

    public function toBroadcast($notifiable): object;
}
