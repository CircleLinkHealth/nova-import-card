<?php

namespace App\CLH\CCD\Importer\ValidationStrategies\Compound;


use App\CLH\CCD\Importer\ValidationStrategies\ValidEndDate;
use App\CLH\CCD\Importer\ValidationStrategies\ValidStartDateNoEndDate;
use App\CLH\Contracts\CCD\ValidationStrategy;

class Nestor implements ValidationStrategy
{

    public function validate($ccdItem)
    {
        $strategies = [
            new ValidEndDate(),
            new ValidStartDateNoEndDate()
        ];

        foreach ( $strategies as $strategy ) {
            $validtation = $strategy->validate( $ccdItem );
            if ( !$validtation ) continue;
            return $validtation;
        }

        return false;
    }
}