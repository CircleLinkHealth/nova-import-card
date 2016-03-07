<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;


use App\CLH\Contracts\CCD\ValidationStrategy;

class ValidStatus implements ValidationStrategy
{
    public function validate($ccdItem)
    {
        if (! is_object($ccdItem)) return false;

        return empty($status = $ccdItem->status) ? false : in_array( strtolower( $status ), ['active', 'chronic'] );
    }
}