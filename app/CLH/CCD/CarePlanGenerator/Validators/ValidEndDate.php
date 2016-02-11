<?php

namespace App\CLH\CCD\CarePlanGenerator\Validators;

use App\CLH\Contracts\CCD\Validator;
use Carbon\Carbon;

class ValidEndDate implements Validator
{
    public function validate($ccdItem)
    {
        $endDate = '';

        if ( !empty($ccdItem->date_range->end) ) {
            $endDate = Carbon::createFromTimestamp( strtotime( $ccdItem->date_range->end ) );
        }

        return (!empty($endDate) && !$endDate->isPast());
    }
}