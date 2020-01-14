<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Exceptions;

class InvalidCcdaException extends \Exception
{
    public function __construct($ccdaId = null)
    {
        $message = 'Invalid Ccda';

        if ($ccdaId) {
            $message .= " id:$ccdaId";
        }

        parent::__construct($message, 422);
    }
}
