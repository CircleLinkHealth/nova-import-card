<?php

namespace App\CLH\CCD\CarePlanGenerator\Validators;


use App\CLH\Contracts\CCD\Validator;

class ValidStatus implements Validator
{
    public function validate($ccdItem)
    {
        return in_array( strtolower( $ccdItem->status ), ['active', 'chronic'] );
    }
}