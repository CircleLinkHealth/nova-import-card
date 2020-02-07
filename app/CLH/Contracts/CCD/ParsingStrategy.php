<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Contracts\CCD;

interface ParsingStrategy
{
    public function parse(
        \CircleLinkHealth\SharedModels\Entities\Ccda $ccda,
        ValidationStrategy $validator = null
    );
}
