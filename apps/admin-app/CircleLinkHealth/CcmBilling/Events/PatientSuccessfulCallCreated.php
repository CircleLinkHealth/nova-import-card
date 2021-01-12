<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use CircleLinkHealth\CcmBilling\Contracts\CanDebounceJobForPatient;
use Illuminate\Queue\SerializesModels;

class PatientSuccessfulCallCreated implements CanDebounceJobForPatient
{
    use SerializesModels;

    protected int $patientUserId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $patientUserId)
    {
        $this->patientUserId = $patientUserId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

    public function debounceDuration(): int
    {
        return 0;
    }

    public function getPatientId(): int
    {
        return $this->patientUserId;
    }

    public function shouldDebounce(): bool
    {
        return false;
    }
}
