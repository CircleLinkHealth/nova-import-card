<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ValidStartDateNoEndDate implements ValidationStrategy
{

    public function validate($ccdItem)
    {
        return (!empty($ccdItem->date_range->start) && empty($ccdItem->date_range->end));
    }
}