<?php

namespace App\CLH\Contracts\CCD;

use App\CLH\CCD\Ccda;

interface ParsingStrategy
{
    public function parse(Ccda $ccda, ValidationStrategy $validator = null);
}