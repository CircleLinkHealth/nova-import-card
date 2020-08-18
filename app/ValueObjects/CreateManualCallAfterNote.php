<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;

class CreateManualCallAfterNote implements Arrayable
{
    /**
     * @var mixed|string
     */
    private $callStatus;
    /**
     * @var User|User
     */
    private $patient;

    /**
     * CreateManualCallAfterNote constructor.
     * @param mixed|string $lastCallStatus
     */
    public function __construct(User $patient, string $lastCallStatus)
    {
        $this->patient    = $patient;
        $this->callStatus = $lastCallStatus;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'callStatus' => $this->callStatus,
            'patient'    => $this->patient,
        ];
    }
    
    /**
     * @return mixed|string
     */
    public function getCallStatus()
    {
        return $this->callStatus;
    }
    
    /**
     * @return User
     */
    public function getPatient(): User
    {
        return $this->patient;
    }
}
