<?php

namespace App\CLH\Contracts\CCD;

interface ParsingStrategy
{
    public function parse($ccdSection, ValidationStrategy $validator = null);
}