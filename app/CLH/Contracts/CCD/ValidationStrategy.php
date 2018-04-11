<?php

namespace App\CLH\Contracts\CCD;

interface ValidationStrategy
{
    public function validate($ccdItem);
}
