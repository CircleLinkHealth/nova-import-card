<?php

namespace App\CLH\Contracts\CCD;

interface ParserWithValidation
{
    public function parse($ccdSection, Validator $validator);
}