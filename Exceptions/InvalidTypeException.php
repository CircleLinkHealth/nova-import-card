<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Exceptions;

use Throwable;

class InvalidTypeException extends \Exception
{
    public function __construct($message = 'Invalid Type', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
