<?php

namespace App\CLH\CCD\Importer\ValidationStrategies\Compound;


use App\CLH\Contracts\CCD\ValidationStrategy;
use App\Importer\Section\Validators\ValidEndDate;
use App\Importer\Section\Validators\ValidStartDateNoEndDate;

class Nestor implements ValidationStrategy
{

    public function validate($ccdItem)
    {
        $strategies = [
            new ValidEndDate(),
            new ValidStartDateNoEndDate()
        ];

        foreach ( $strategies as $strategy ) {
            $isActive = $strategy->validate( $ccdItem );
            if ( !$isActive ) continue;

            return $isActive;
        }

        return false;
    }
}