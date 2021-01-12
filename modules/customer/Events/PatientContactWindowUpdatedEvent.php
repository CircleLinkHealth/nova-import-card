<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Events;

use CircleLinkHealth\Customer\Entities\PatientContactWindow;

/**
 * Class PatientContactWindowUpdatedEvent.
 */
class PatientContactWindowUpdatedEvent
{
    /**
     * @var PatientContactWindow[]
     */
    public $windows;

    public function __construct(iterable $windows)
    {
        $this->windows = $windows;
    }
}
