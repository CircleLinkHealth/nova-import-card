<?php


use Carbon\Carbon;
use Illuminate\Support\Collection;

if (!function_exists('formatPhoneNumber')) {
    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx
     * @param $string
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


if (!function_exists('parseDaysStringToNumbers')) {
    /**
     * Parses a String of days into numbers.
     *
     * @param $daysAsString
     * @param string $delimiter
     * @return string
     */
    function parseDaysStringToNumbers($daysAsString, $delimiter = ',')
    {
        if (empty($daysAsString)) return [];

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
     * @return string
     */
    function validateBloodPressureString($bloodPressureString, $delimiter = '/')
    {
        if (empty($bloodPressureString)) return true;

        $readings = new Collection(explode($delimiter, $bloodPressureString));

        foreach ($readings as $reading)
        {
            if (!is_numeric($reading) || $reading > 999 || $reading < 10) return false;
        }

        return true;
    }
}