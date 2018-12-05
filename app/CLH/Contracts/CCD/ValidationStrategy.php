<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Contracts\CCD;

interface ValidationStrategy
{
    public function validate($ccdItem);
}
