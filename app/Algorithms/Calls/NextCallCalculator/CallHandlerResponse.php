<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallCalculator;

use Carbon\Carbon;

class CallHandlerResponse
{
    public string $attemptNote;
    public Carbon $nextCallDate;
    public string $reasoning;

    public function __construct(Carbon $nextCallDate, string $reasoning, string $attemptNote = '')
    {
        $this->nextCallDate = $nextCallDate;
        $this->reasoning    = $reasoning;
        $this->attemptNote  = $attemptNote;
    }
}
