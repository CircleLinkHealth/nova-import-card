<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallSuggestor;

use Carbon\Carbon;

class HandlerResponse
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
