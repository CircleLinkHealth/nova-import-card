<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;

use App\CLH\Contracts\CCD\ValidationStrategy;
use Carbon\Carbon;

class ValidEndDate implements ValidationStrategy
{
    /**
     * Make sure an end date exists, and that it is in the future
     * @param $ccdItem
     * @return bool
     */
    public function validate($ccdItem)
    {
        $endDate = '';

        if ( !empty($ccdItem->end) ) {
            $endDate = Carbon::createFromTimestamp( strtotime( $ccdItem->end ) );
        }

        return (!empty($endDate) && !$endDate->isPast());
    }
}