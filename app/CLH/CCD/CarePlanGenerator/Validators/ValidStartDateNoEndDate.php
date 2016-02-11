<?php

namespace App\CLH\CCD\CarePlanGenerator\Validators;


use App\CLH\Contracts\CCD\Validator;

class ValidStartDateNoEndDate implements Validator
{

    public function validate($ccdItem)
    {
        return (!empty($ccdItem->date_range->start) && empty($ccdItem->date_range->end));
    }
}