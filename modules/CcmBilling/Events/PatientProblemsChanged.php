<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use CircleLinkHealth\CcmBilling\Contracts\CanDebounceJobForPatient;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Queue\SerializesModels;

class PatientProblemsChanged implements CanDebounceJobForPatient
{
    use SerializesModels;

    protected int $patientUserId;

    protected bool $shouldDebounce;

    /**
     * Create a new event instance.
     *
     * @param  mixed $shouldDebounce
     * @return void
     */
    public function __construct(int $patientUserId, $shouldDebounce = true)
    {
        $this->patientUserId  = $patientUserId;
        $this->shouldDebounce = $shouldDebounce;
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
        return CpmConstants::FIVE_MINUTES_IN_SECONDS;
    }

    public function getPatientId(): int
    {
        return $this->patientUserId;
    }

    public function shouldDebounce(): bool
    {
        return $this->shouldDebounce;
    }
}
