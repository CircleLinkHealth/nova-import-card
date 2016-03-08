<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ValidStartDateNoEndDate implements ValidationStrategy
{

    public function validate($ccdItem)
    {
        return (!empty($ccdItem->start) && empty($ccdItem->end));
    }
}