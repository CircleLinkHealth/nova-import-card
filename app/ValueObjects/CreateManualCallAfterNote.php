<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

use App\Contracts\CallHandler;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;

class CreateManualCallAfterNote implements Arrayable
{
    private CallHandler $callHandler;
    /**
     * @var User|User
     */
    private User $patient;

    /**
     * CreateManualCallAfterNote constructor.
     * @param mixed|string $lastCallStatus
     */
    public function __construct(User $patient, CallHandler $callHandler)
    {
        $this->patient     = $patient;
        $this->callHandler = $callHandler;
    }

    /**
     * @return mixed|string
     */
    public function getCallHandler()
    {
        return $this->callHandler;
    }

    public function getPatient(): User
    {
        return $this->patient;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'callHandler' => $this->callHandler,
            'patient'     => $this->patient,
        ];
    }
}
