<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Exceptions;

use Throwable;

class CsvFieldNotFoundException extends \Exception
{
    public function __construct($message = 'Csv Field Not Found', $code = 422, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
