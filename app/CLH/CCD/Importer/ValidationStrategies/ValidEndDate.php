<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;

use App\CLH\Contracts\CCD\ValidationStrategy;
use Carbon\Carbon;

class ValidEndDate implements ValidationStrategy
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