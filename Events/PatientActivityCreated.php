<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Events;

use App\Constants;
use CircleLinkHealth\CcmBilling\Contracts\CanDebounceJobForPatient;
use Illuminate\Queue\SerializesModels;

class PatientActivityCreated implements CanDebounceJobForPatient
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
        return Constants::FIVE_MINUTES_IN_SECONDS;
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
