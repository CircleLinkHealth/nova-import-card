<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ValidStatus implements ValidationStrategy
{
    public function validate($ccdItem)
    {
        return in_array( strtolower( $ccdItem->status ), ['active', 'chronic'] );
    }
}