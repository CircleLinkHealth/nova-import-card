<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ValidStatus implements ValidationStrategy
{
    public function validate($ccdItem)
    {
        if (empty($ccdItem->status)) return false;

        return in_array( strtolower( $ccdItem->status ), ['active', 'chronic'] );
    }
}