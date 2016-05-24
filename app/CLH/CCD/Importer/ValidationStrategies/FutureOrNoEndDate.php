<?php

namespace App\CLH\CCD\Importer\ValidationStrategies;

use App\CLH\Contracts\CCD\ValidationStrategy;
use Carbon\Carbon;

class FutureOrNoEndDate implements ValidationStrategy
{
    /**
     * Always import unless there is an end date that has passed.
     *
     * @param $ccdItem
     * @return bool
     */
    public function validate($ccdItem)
    {
        $endDate = Carbon::tomorrow();

        if ( !empty($ccdItem->end) )
        {
            $endDate = Carbon::createFromTimestamp( strtotime( $ccdItem->end ) );
        }

        return !$endDate->isPast();
    }
}