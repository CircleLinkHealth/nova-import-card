<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use Illuminate\Queue\SerializesModels;

class PatientConsentedToService
{
    use SerializesModels;

    protected int $patientUserId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $patientUserId, string $serviceCode)
    {
        $this->patientUserId = $patientUserId;
        $this->serviceCode   = $serviceCode;
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

    public function getPatientId(): int
    {
        return $this->patientUserId;
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }
}
