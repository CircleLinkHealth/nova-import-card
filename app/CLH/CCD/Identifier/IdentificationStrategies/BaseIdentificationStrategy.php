<?php

namespace App\CLH\CCD\Identifier\IdentificationStrategies;


use App\CLH\Contracts\CCD\IdentificationStrategy;
use App\ParsedCCD;

abstract class BaseIdentificationStrategy implements IdentificationStrategy
{
    protected $ccd;

    public function __construct($ccd)
    {
        $this->ccd = $ccd;
    }
}