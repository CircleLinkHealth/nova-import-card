<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

trait ValidatesDates
{
    public function isValidDate(string $date = null)
    {
        return \Validator::make(['date' => $date], ['date' => 'required|date'])->passes();
    }
}