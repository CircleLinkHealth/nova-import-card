<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Exceptions;

use CircleLinkHealth\SharedModels\Entities\TargetPatient;
use Throwable;

class CcdaWasNotFetchedFromAthenaApi extends \Exception
{
    public function __construct(TargetPatient $targetPatient, string $message = 'CCDA was not fetched from Athena API', int $code = 500, Throwable $previous = null)
    {
        $message .= 'for '.get_class($targetPatient).':'.$targetPatient->id;

        parent::__construct($message, $code, $previous);
    }
}
