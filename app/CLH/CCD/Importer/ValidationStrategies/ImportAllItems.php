<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ImportAllItems implements ValidationStrategy
{
    public function validate($ccdItem)
    {
        //Since we wanna import all items, we always return true
        return true;
    }
}