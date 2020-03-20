<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use CircleLinkHealth\Customer\Entities\User;

class CarePlanWasProviderApproved extends Event
{
    /**
     * @var User
     */
    public $patient;

    /**
     * CarePlanWasProviderApproved constructor.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }
}
