<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Events;

use CircleLinkHealth\Customer\Entities\User;

class CarePlanWasProviderApproved
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
