<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exceptions;

use Throwable;

class FileNotFoundException extends \Exception
{
    public function __construct($message = 'File Not Found', $code = 404, Throwable $previous = null)
    {
    }
}
