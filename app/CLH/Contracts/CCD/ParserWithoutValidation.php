<?php

namespace App\CLH\Contracts\CCD;


interface ParserWithoutValidation
{
    public function parse($ccdSection);
}