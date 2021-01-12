<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Raygun\Entities;

use Throwable;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 3/15/19
 * Time: 11:42 PM.
 */
class LogMessage extends \Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        $message = "$message. at {$this->getFile()}:{$this->getLine()}";

        parent::__construct($message, $code, $previous);
    }
}
