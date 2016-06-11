<?php

namespace App\CLH\Contracts\CCD;

use App\Models\CCD\Ccda;

interface ParsingStrategy
{
    public function parse(\App\Models\CCD\Ccda $ccda, ValidationStrategy $validator = null);
}