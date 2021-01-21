<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Helpers;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;

class Utils
{
    public static function getCssToHideEditButton($row)
    {
        return "<style>
a[dusk='{$row->id}-edit-button'], a[dusk='edit-resource-button'] {
    display: none;
}
</style>";
    }

    public static function parseExcelDate($date = null):?Carbon
    {
        if (empty($date)) {
            return null;
        }

        if (is_numeric($date)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
        }

        if ( ! $date) {
            return null;
        }

        try {
            $date = ImportPatientInfo::parseDOBDate($date);
        } catch (\Throwable $throwable) {
            $date = null;
        }

        return $date;
    }
}
