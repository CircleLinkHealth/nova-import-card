<?php


use Carbon\Carbon;
use Illuminate\Support\Collection;

if (!function_exists('formatPhoneNumber')) {
    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx
     *
     * @param $string
     *
     * @return string
     */
    function formatPhoneNumber($string)
    {
        $sanitized = extractNumbers($string);

        if (strlen($sanitized) < 10) {
            return false;
        }

        if (strlen($sanitized) > 10) {
            $sanitized = substr($sanitized, -10);
        }

        return strlen($sanitized) == 10
            ? substr($sanitized, 0, 3) . '-' . substr($sanitized, 3, 3) . '-' . substr($sanitized, 6, 4)
            : null;
    }
}

if (!function_exists('extractNumbers')) {
    /**
     * Returns only numerical values in a string
     *
     * @param $string
     *
     * @return string
     */
    function extractNumbers($string)
    {
        preg_match_all('/([\d]+)/', $string, $match);

        return implode($match[0]);
    }
}

if (!function_exists('parseCsvToArray')) {
    /**
     * Parses a CSV file into an array.
     *
     * @param $file
     *
     * @return string
     */
    function parseCsvToArray($file)
    {
        $csvArray = $fields = [];
        $i = 0;
        $handle = @fopen($file, "r");
        if ($handle) {
            while (($row = fgetcsv($handle, 4096)) !== false) {
                if (empty($fields)) {
                    $fields = array_map('trim', $row);
                    continue;
                }
                foreach ($row as $k => $value) {
                    $csvArray[$i][$fields[$k]] = trim($value);
                }
                $i++;
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        return $csvArray;
    }
}

if (!function_exists('secondsToHHMM')) {
    function secondsToHHMM($seconds)
    {
        $getHours = floor($seconds / 3600);
        $getMins = floor(($seconds - ($getHours * 3600)) / 60);

        return $getHours . ':' . $getMins;
    }
}

if (!function_exists('secondsToMMSS')) {
    function secondsToMMSS($seconds)
    {
        $minutes = sprintf('%02d', floor($seconds / 60));
        $seconds = sprintf(':%02d', (int) $seconds % 60);

        return $minutes . $seconds;
    }
}




if (!function_exists('parseDaysStringToNumbers')) {
    /**
     * Parses a String of days into numbers.
     *
     * @param $daysAsString
     * @param string $delimiter
     *
     * @return string
     */
    function parseDaysStringToNumbers(
        $daysAsString,
        $delimiter = ','
    ) {
        if (empty($daysAsString)) {
            return [];
        }

        //eg. Monday, Tuesday, Wednesday
        $daysString = new Collection(explode($delimiter, $daysAsString));

        // 1 for Monday, 2 for Tuesday, blah, blah
        $daysNumber = $daysString->map(function ($day) {
            return Carbon::parse("Next $day")->dayOfWeek;
        })->toArray();

        return $daysNumber;
    }
}


if (!function_exists('validateBloodPressureString')) {
    /**
     * Validates blood pressure string that looks like this: xxx/xxx
     *
     * @param $bloodPressureString
     * @param string $delimiter
     *
     * @return string
     */
    function validateBloodPressureString(
        $bloodPressureString,
        $delimiter = '/'
    ) {
        if (empty($bloodPressureString)) {
            return true;
        }

        $readings = new Collection(explode($delimiter, $bloodPressureString));

        foreach ($readings as $reading) {
            if (!is_numeric($reading) || $reading > 999 || $reading < 10) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('carbonToClhDayOfWeek')) {
    /**
     * Convert Carbon DayOfWeek to CLH DayOfWeek.
     * Carbon does 0-6 for Sun-Sat.
     * We do 1-7 for Mon-Sun.
     *
     * @param $dayOfWeek
     *
     * @return int
     */
    function carbonToClhDayOfWeek($dayOfWeek)
    {
        return $dayOfWeek == 0
            ? 7
            : $dayOfWeek;
    }
}

if (!function_exists('clhDayOfWeekToDayName')) {
    /**
     * Convert CLH DayOfWeek to a day such as Monday, Tuesday
     *
     * @param $dayOfWeek
     *
     * @return int
     */
    function clhDayOfWeekToDayName($clhDayOfWeek)
    {
        $days = [
            '',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        return $days[$clhDayOfWeek];
    }
}

if (!function_exists('timestampsToWindow')) {
    /**
     * Convert timestamps to a Contact Window.
     *
     * @param $startTimestamp
     * @param $endTimestamp
     * @param string $timezone
     *
     * @return array
     */
    function timestampsToWindow(
        $startTimestamp,
        $endTimestamp,
        $timezone = 'America/New_York'
    ) {
        $startDate = Carbon::parse($startTimestamp, $timezone);
        $endDate = Carbon::parse($endTimestamp, $timezone);

        return [
            'day'   => carbonToClhDayOfWeek($startDate->dayOfWeek),
            'start' => $startDate->format('H:i:s'),
            'end'   => $endDate->format('H:i:s'),
        ];
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * uses mt_rand to give a random string.
     *
     * @return string
     */
    function generateRandomString($l, $c = 'abcdefghijklmnopqrstuvwxyz1234567890') {
        for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
        return $s;
    }
}



if (!function_exists('windowToTimestamps')) {
    /**
     * Convert timestamps to a Contact Window.
     *
     * @param $startTimestamp
     * @param $endTimestamp
     * @param string $timezone
     *
     * @return array
     */
    function clhWindowToTimestamps(
        $date,
        $start,
        $end
    ) {
        $startDate = Carbon::parse($date);

        $startTimeH = Carbon::parse($start)->format('H');
        $startTimei = Carbon::parse($start)->format('i');

        $startDate = $startDate->setTime($startTimeH, $startTimei)->toDateTimeString();

        $endDate = Carbon::parse($date);

        $endTimeH = Carbon::parse($end)->format('H');
        $endTimei = Carbon::parse($end)->format('i');

        $endDate = $endDate->setTime($endTimeH, $endTimei)->toDateTimeString();;

        return [
            'window_start' => $startDate,
            'window_end'   => $endDate,
        ];
    }
}

if (!function_exists('dateAndTimeToCarbon')) {
    /**
     * Convert a Date and Time Object to one Carbon Object.
     *
     * @param $date
     * @param $time
     *
     * @return Carbon window
     */
    function dateAndTimeToCarbon(
        $date,
        $time
    ) {

        $carbon_date = Carbon::parse($date);

        $carbon_hour = Carbon::parse($time)->format('H');
        $carbon_minutes = Carbon::parse($time)->format('i');
        $carbon_date->setTime($carbon_hour, $carbon_minutes);

        return $carbon_date;
    }
}


if (!function_exists('secondsToHMS')) {
    /**
     * Converts a string of time in seconds to H:m:s
     *
     * @param $totalTimeInSeconds
     * @param string $delimiter
     *
     * @return string
     */
    function secondsToHMS(
        $totalTimeInSeconds,
        $delimiter = ':'
    ) {

        $H2 = floor($totalTimeInSeconds / 3600);
        $m2 = ($totalTimeInSeconds / 60) % 60;
        $s2 = $totalTimeInSeconds % 60;

        return sprintf("%02d$delimiter%02d$delimiter%02d", $H2, $m2, $s2);
    }
}


if (!function_exists('timezones')) {
    /**
     * Get the timezones we support.
     *
     * @return array|string
     * @internal param $totalTimeInSeconds
     * @internal param string $delimiter
     */
    function timezones() : array
    {
        return [
            'America/New_York'    => 'Eastern Time',
            'America/Chicago'     => 'Central Time',
            'America/Denver'      => 'Mountain Time',
            'America/Phoenix'     => 'Mountain Time (no DST)',
            'America/Los_Angeles' => 'Pacific Time',
            'America/Anchorage'   => 'Alaska Time',
            'America/Adak'        => 'Hawaii-Aleutian',
            'Pacific/Honolulu'    => 'Hawaii-Aleutian Time (no DST)',
        ];
    }
}


