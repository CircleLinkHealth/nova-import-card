<?php

namespace App\CLH\CCD\CarePlanGenerator\Validators;


use App\CLH\Contracts\CCD\Validator;

class ImportAllItems implements Validator
{
    public function validate($ccdItem)
    {
        //Since we wanna import all items, we always return true
        return true;
    }
}