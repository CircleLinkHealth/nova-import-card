<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Helpers;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Illuminate\Support\Str;

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

    public static function parseDate($date = null): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            $date = Carbon::parse($date);

            if ($date->isToday()) {
                throw new \Exception('date note parsed correctly');
            }

            return $date;
        } catch (\Throwable $e) {
            if ($date instanceof Carbon) {
                $date = $date->toDateString();
            }

            if (Str::contains($date, '/')) {
                $delimiter = '/';
            } elseif (Str::contains($date, '-')) {
                $delimiter = '-';
            }
            $date = explode($delimiter, $date);

            if (count($date) < 3) {
                return null;
            }

            $year = $date[2];

            if (2 == strlen($year)) {
                //if date is two digits we are assuming it's from the 1900s
                $year = (int) $year + 1900;
            }

            return Carbon::createFromDate($year, $date[0], $date[1]);
        }
    }

    public static function parseExcelDate($date = null, bool $isDob = true): ?Carbon
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

        if ( ! $isDob) {
            return self::parseDate($date);
        }

        try {
            $date = ImportPatientInfo::parseDOBDate($date);
        } catch (\Throwable $throwable) {
            $date = null;
        }

        return $date;
    }
}
