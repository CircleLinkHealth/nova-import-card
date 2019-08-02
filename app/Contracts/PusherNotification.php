<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface PusherNotification
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array|\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn();

    public function getPatientId(): int;

    /**
     * @return array
     */
    public function receivers(): array;

    public function setDataToPusher();
}
