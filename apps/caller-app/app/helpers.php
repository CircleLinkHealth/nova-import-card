<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\AppConfig;
use App\CarePlanTemplate;
use App\Constants;
use App\Exceptions\CsvFieldNotFoundException;
use App\Jobs\SendSlackMessage;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

if ( ! function_exists('abort_if_str_contains_unsafe_characters')) {
    function abort_if_str_contains_unsafe_characters(string $string)
    {
        if (str_contains_unsafe_characters($string)) {
            abort(404);
        }
    }
}

if ( ! function_exists('str_contains_unsafe_characters')) {
    function str_contains_unsafe_characters(string $string)
    {
        return str_contains($string, ['<', '>', '&', '=']);
    }
}

if ( ! function_exists('parseIds')) {
    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param mixed $value
     *
     * @return array
     */
    function parseIds($value)
    {
        if ($value instanceof Model) {
            return [$value->getKey()];
        }

        if ($value instanceof EloquentCollection) {
            return $value->modelKeys();
        }

        if (is_array($value)) {
            $value = collect($value);
        }

        if ($value instanceof Collection) {
            return $value->map(
                function ($el) {
                    $id = parseIds($el);

                    return $id[0];
                }
            )->values()->toArray();
        }

        if (is_string($value) && str_contains($value, ',')) {
            return explode(',', $value);
        }

        return array_filter((array)$value);
    }
}

if ( ! function_exists('safeStartOfMonthQuery')) {
    /**
     * Return a start of month query compadible with both sqlite and mysql.
     *
     * @return string
     */
    function safeStartOfMonthQuery()
    {
        return 'mysql' === config('database.connections')[config('database.default')]['driver']
            ? "DATE_ADD(DATE_ADD(LAST_DAY(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')), INTERVAL 1 DAY), INTERVAL - 1 MONTH)"
            : "date('now','start of month')"; //sqlite
    }
}

if ( ! function_exists('isOnSqlite')) {
    /**
     * Is the app running on sqlite?
     *
     * @return bool
     */
    function isOnSqlite()
    {
        return 'sqlite' === strtolower(config('database.default'));
    }
}

if ( ! function_exists('str_substr_after')) {
    /**
     * Get the substring after the given character.
     *
     * @param $string
     * @param string $character
     *
     * @return string
     */
    function str_substr_after($string, $character = '/')
    {
        $pos = strrpos($string, $character);

        return false === $pos
            ? $string
            : substr($string, $pos + 1);
    }
}

if ( ! function_exists('activeNurseNames')) {
    /**
     * Returns an array of nurse names keyed by id.
     *
     * @return mixed
     */
    function activeNurseNames()
    {
        return User::ofType('care-center')
                   ->where('user_status', 1)
                   ->pluck('display_name', 'id');
    }
}

if ( ! function_exists('sendSlackMessage')) {
    /**
     * Sends a message to Slack.
     *
     * @param string $to - slack channel (should start with '#')
     * @param string $message
     * @param bool $force - in case you really want the message to go to slack (testing | debugging)
     */
    function sendSlackMessage($to, $message, $force = false)
    {
        if ( ! $force && ! in_array(app()->environment(), ['production', 'worker'])) {
            return;
        }

        SendSlackMessage::dispatch($to, $message)->onQueue('default');
    }
}

if ( ! function_exists('formatPhoneNumber')) {
    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx.
     *
     * @param $string
     *
     * @return string
     */
    function formatPhoneNumber($string)
    {
        $sanitized = extractNumbers($string);

        if (10 > strlen($sanitized)) {
            return false;
        }

        if (10 < strlen($sanitized)) {
            $sanitized = substr($sanitized, -10);
        }

        if (10 === strlen($sanitized)) {
            return substr($sanitized, 0, 3) . '-' . substr($sanitized, 3, 3) . '-' . substr($sanitized, 6, 4);
        }

        return null;
    }
}

if ( ! function_exists('formatPhoneNumberE164')) {
    /**
     * Formats a string of numbers as a phone number delimited by dashes as such: xxx-xxx-xxxx.
     *
     * @param $string
     * @param mixed $countryCode
     *
     * @return string
     */
    function formatPhoneNumberE164(
        $string,
        $countryCode = '1'
    ) {
        $sanitized = extractNumbers($string);

        if (strlen($sanitized) < 10) {
            return '';
        }

        if (strlen($sanitized) > 10) {
            $sanitized = substr($sanitized, -10);
        }

        return '+' . $countryCode . $sanitized;
    }
}

if ( ! function_exists('extractNumbers')) {
    /**
     * Returns only numerical values in a string.
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

if ( ! function_exists('detectDelimiter')) {
    /**
     * @param bool|resource $csvFileHandle The handle of a file opened with fopen
     * @param int $length
     *
     * @return false|int|string
     */
    function detectDelimiter($csvFileHandle, $length = 4096)
    {
        $delimiters = [
            ','  => 0,
            "\t" => 0,
            ';'  => 0,
            '|'  => 0,
        ];

        foreach ($delimiters as $delimiter => &$count) {
            $firstLine = fgetcsv($csvFileHandle, $length, $delimiter);
            $count     = count($firstLine);
            rewind($csvFileHandle);
        }

        return array_search(max($delimiters), $delimiters);
    }
}

if ( ! function_exists('parseCsvToArray')) {
    /**
     * Parses a CSV file into an array.
     *
     * @param $file
     * @param int $length
     * @param null $delimiter
     *
     * @return array
     * @throws CsvFieldNotFoundException
     *
     */
    function parseCsvToArray($file, $length = 0, $delimiter = null)
    {
        $csvArray = $fields = [];
        $i        = 0;
        $handle   = @fopen($file, 'r');

        if ( ! $handle) {
            throw new \Exception('Could not read CSV file.');
        }

        $delimiter = $delimiter ?? detectDelimiter($handle);

        while (false !== ($row = fgetcsv($handle, $length, $delimiter))) {
            if (empty($fields)) {
                $row = array_map('strtolower', $row);

                $row = array_map(
                    function ($string) {
                        return str_replace(' ', '_', $string);
                    },
                    $row
                );

                $fields = array_map('trim', $row);
                continue;
            }
            foreach ($row as $k => $value) {
                if ( ! array_key_exists($k, $fields)) {
                    throw new CsvFieldNotFoundException(
                        "Could not find CSV Field with index $k. Check row number $i for bad data."
                    );
                }
                $csvArray[$i][$fields[$k]] = trim($value);
            }
            ++$i;
        }
        if ( ! feof($handle)) {
            throw new \Exception('Error: unexpected fgets() fail.');
        }
        fclose($handle);

        return $csvArray;
    }
}

if ( ! function_exists('iterateCsv')) {
    /**
     * Parses a CSV file into an array.
     *
     * @param $file
     * @param int $length
     * @param null $delimiter
     * @param null $callback
     * @param bool $firstRowContainsColumnHeaders
     * @param mixed $logAndReturnAllActivity
     *
     * @return array
     */
    function iterateCsv($file, $callback = null, $logAndReturnAllActivity = false, $length = 0, $delimiter = null)
    {
        $results = $fields = $errors = [];
        $i       = 0;
        $handle  = @fopen($file, 'r');

        if ( ! $handle) {
            throw new \Exception('Could not read CSV file.');
        }

        $delimiter = $delimiter ?? detectDelimiter($handle);

        while (false !== ($row = fgetcsv($handle, $length, $delimiter))) {
            $csvRowArray = [];

            if (empty($fields)) {
                $row = array_map('strtolower', $row);

                $row = array_map(
                    function ($string) {
                        return str_replace(' ', '_', $string);
                    },
                    $row
                );

                $fields = array_map('trim', $row);
                continue;
            }
            foreach ($row as $k => $value) {
                if ( ! array_key_exists($k, $fields)) {
                    $errors[] = [
                        'row_number' => $i,
                        'message'    => "Could not find CSV Field with index $k. Check row number $i for bad data.",
                    ];

                    continue 2;
                }
                $csvRowArray[$fields[$k]] = trim($value);
            }

            if (isset($callback)) {
                $cb = call_user_func($callback, $csvRowArray);

                if ($logAndReturnAllActivity) {
                    $results[] = $cb;
                }

                if (array_key_exists('error', $cb)) {
                    $errors[] = $cb['error'];
                }
            }

            ++$i;
        }
        if ( ! feof($handle)) {
            throw new \Exception('Error: unexpected fgets() fail.');
        }
        fclose($handle);

        return [
            'results' => $results,
            'errors'  => $errors,
        ];
    }
}

if ( ! function_exists('secondsToHHMM')) {
    function secondsToHHMM($seconds)
    {
        $getHours = sprintf('%02d', floor($seconds / 3600));
        $getMins  = sprintf('%02d', floor(($seconds - ($getHours * 3600)) / 60));

        return $getHours . ':' . $getMins;
    }
}

if ( ! function_exists('secondsToMMSS')) {
    function secondsToMMSS($seconds)
    {
        $minutes = sprintf('%02d', floor($seconds / 60));
        $seconds = sprintf(':%02d', (int)$seconds % 60);

        return $minutes . $seconds;
    }
}

if ( ! function_exists('parseDaysStringToNumbers')) {
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
        $daysNumber = $daysString->map(
            function ($day) {
                return Carbon::parse("Next ${day}")->dayOfWeek;
            }
        )->toArray();

        return $daysNumber;
    }
}

if ( ! function_exists('validateBloodPressureString')) {
    /**
     * Validates blood pressure string that looks like this: xxx/xxx.
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
            if ( ! is_numeric($reading) || $reading > 999 || $reading < 10) {
                return false;
            }
        }

        return true;
    }
}

if ( ! function_exists('carbonGetNext')) {
    /**
     * Get carbon instance of the next $day.
     *
     * @param $day
     *
     * @return Carbon|false
     */
    function carbonGetNext($day = 'monday')
    {
        if ( ! is_numeric($day)) {
            $dayOfWeek = clhToCarbonDayOfWeek(dayNameToClhDayOfWeek($day));
            $dayName   = $day;
        }

        if (is_numeric($day)) {
            $dayOfWeek = clhToCarbonDayOfWeek($day);
            $dayName   = clhDayOfWeekToDayName($day);
        }

        if ( ! isset($dayOfWeek)) {
            return false;
        }

        $now = Carbon::now();

        if ($now->dayOfWeek == $dayOfWeek) {
            return $now;
        }

        return $now->parse("next ${dayName}");
    }
}

if ( ! function_exists('clhToCarbonDayOfWeek')) {
    /**
     * Convert CLH DayOfWeek to Carbon DayOfWeek.
     * Carbon does 0-6 for Sun-Sat.
     * We do 1-7 for Mon-Sun.
     *
     * @param $dayOfWeek
     *
     * @return int
     */
    function clhToCarbonDayOfWeek($dayOfWeek)
    {
        return 7 == $dayOfWeek
            ? 0
            : $dayOfWeek;
    }
}

if ( ! function_exists('carbonToClhDayOfWeek')) {
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
        return 0 == $dayOfWeek
            ? 7
            : $dayOfWeek;
    }
}

if ( ! function_exists('clhDayOfWeekToDayName')) {
    /**
     * Convert CLH DayOfWeek to a day such as Monday, Tuesday.
     *
     * @param $clhDayOfWeek
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

if ( ! function_exists('dayNameToClhDayOfWeek')) {
    /**
     * Convert a day such as Monday, Tuesday to CLH DayOfWeek (1,2,3,4,5,6,7).
     *
     * @param $clhDayOfWeek
     *
     * @return int
     */
    function dayNameToClhDayOfWeek($clhDayOfWeek)
    {
        $days = [
            'Monday'    => 1,
            'Tuesday'   => 2,
            'Wednesday' => 3,
            'Thursday'  => 4,
            'Friday'    => 5,
            'Saturday'  => 6,
            'Sunday'    => 7,
        ];

        return $days[ucfirst(strtolower(trim($clhDayOfWeek)))] ?? false;
    }
}

if ( ! function_exists('weekDays')) {
    /**
     * Returns the days of the week.
     *
     * @return array
     */
    function weekDays()
    {
        return [
            1 => 'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];
    }
}

if ( ! function_exists('timestampsToWindow')) {
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
        $endDate   = Carbon::parse($endTimestamp, $timezone);

        return [
            'day'   => carbonToClhDayOfWeek($startDate->dayOfWeek),
            'start' => $startDate->format('H:i:s'),
            'end'   => $endDate->format('H:i:s'),
        ];
    }
}

if ( ! function_exists('generateRandomString')) {
    /**
     * uses mt_rand to give a random string.
     *
     * @param mixed $l
     * @param mixed $c
     *
     * @return string
     */
    function generateRandomString(
        $l,
        $c = 'abcdefghijklmnopqrstuvwxyz1234567890'
    ) {
        for ($s = '', $cl = strlen($c) - 1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i) {
        }

        return $s;
    }
}

if ( ! function_exists('windowToTimestamps')) {
    /**
     * Convert timestamps to a Contact Window.
     *
     * @param $startTimestamp
     * @param $endTimestamp
     * @param string $timezone
     * @param mixed $date
     * @param mixed $start
     * @param mixed $end
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

        $endDate = $endDate->setTime($endTimeH, $endTimei)->toDateTimeString();

        return [
            'window_start' => $startDate,
            'window_end'   => $endDate,
        ];
    }
}

if ( ! function_exists('dateAndTimeToCarbon')) {
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

        $carbon_hour    = Carbon::parse($time)->format('H');
        $carbon_minutes = Carbon::parse($time)->format('i');
        $carbon_date->setTime($carbon_hour, $carbon_minutes);

        return $carbon_date;
    }
}

if ( ! function_exists('secondsToHMS')) {
    /**
     * Converts a string of time in seconds to H:m:s.
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

        return sprintf("%02d${delimiter}%02d${delimiter}%02d", $H2, $m2, $s2);
    }
}

if ( ! function_exists('timezones')) {
    /**
     * Get the timezones we support.
     *
     * @return array|string
     *
     * @internal param $totalTimeInSeconds
     * @internal param string $delimiter
     */
    function timezones(): array
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

if ( ! function_exists('defaultCarePlanTemplate')) {
    /**
     * Returns CircleLink's default CarePlanTemplate.
     *
     * @return CarePlanTemplate|null
     */
    function getDefaultCarePlanTemplate(): ?CarePlanTemplate
    {
        $id = getAppConfig('default_care_plan_template_id');

        return CarePlanTemplate::findOrFail($id);
    }
}

if ( ! function_exists('getAppConfig')) {
    /**
     * Returns the AppConfig value for the given key.
     *
     * @param string $key
     *
     * @return string|null
     */
    function getAppConfig(string $key)
    {
        $conf = AppConfig::whereConfigKey($key)->first();

        return $conf
            ? $conf->config_value
            : null;
    }
}

if ( ! function_exists('setAppConfig')) {
    /**
     * Save an AppConfig key, value and then return it.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return CarePlanTemplate
     */
    function setAppConfig(string $key, $value)
    {
        $conf = AppConfig::updateOrCreate(
            [
                'config_key' => $key,
            ],
            [
                'config_value' => $value,
            ]
        );

        return $conf
            ? $conf->config_value
            : null;
    }
}

if ( ! function_exists('snakeToSentenceCase')) {
    /**
     * Convert Snake to Sentence Case.
     *
     * @param $string
     *
     * @return mixed
     */
    function snakeToSentenceCase($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }
}

if ( ! function_exists('linkToDownloadFile')) {
    /**
     * Generate a file to download a file.
     *
     * @param $path
     * @param mixed $absolute
     *
     * @return string
     * @throws Exception
     *
     * @throws Exception
     */
    function linkToDownloadFile($path, $absolute = false)
    {
        if ( ! $path) {
            throw new \Exception('File path cannot be empty');
        }

        return route(
            'download',
            [
                'filePath' => base64_encode($path),
            ],
            $absolute
        );
    }
}

if ( ! function_exists('linkToCachedView')) {
    /**
     * Generate a link to a cached view.
     *
     * @param $viewHashKey
     * @param mixed $absolute
     *
     * @return string
     * @throws Exception
     *
     */
    function linkToCachedView($viewHashKey, $absolute = false)
    {
        if ( ! $viewHashKey) {
            throw new \Exception('File path cannot be empty');
        }

        return route('get.cached.view.by.key', ['key' => $viewHashKey], $absolute);
    }
}

if ( ! function_exists('parseCallDays')) {
    function parseCallDays($preferredCallDays)
    {
        if ( ! $preferredCallDays || str_contains(strtolower($preferredCallDays), ['any'])) {
            return [1, 2, 3, 4, 5];
        }

        $days = [];

        if (str_contains($preferredCallDays, [','])) {
            foreach (explode(',', $preferredCallDays) as $dayName) {
                $days[] = dayNameToClhDayOfWeek($dayName);
            }
        } elseif (str_contains($preferredCallDays, ['-'])) {
            $exploded = explode('-', $preferredCallDays);

            $from = array_search($exploded[0], weekDays());
            $to   = array_search($exploded[1], weekDays());

            for ($i = $from; $i <= $to; ++$i) {
                $days[] = $i;
            }
        } else {
            $days[] = dayNameToClhDayOfWeek($preferredCallDays);
        }

        return array_filter($days);
    }
}

if ( ! function_exists('parseCallTimes')) {
    function parseCallTimes($preferredCallTimes)
    {
        if ( ! $preferredCallTimes) {
            return [
                'start' => '09:00:00',
                'end'   => '17:00:00',
            ];
        }

        $times = [];

        if (str_contains($preferredCallTimes, ['-'])) {
            $delimiter = '-';
        }

        if (str_contains($preferredCallTimes, ['to'])) {
            $delimiter = 'to';
        }

        if (isset($delimiter)) {
            $preferredTimes = explode($delimiter, $preferredCallTimes);
            $times['start'] = Carbon::parse(trim($preferredTimes[0]))->toTimeString();
            $times['end']   = Carbon::parse(trim($preferredTimes[1]))->toTimeString();
        } else {
            $times = [
                'start' => '09:00:00',
                'end'   => '17:00:00',
            ];
        }

        return $times;
    }
}

if ( ! function_exists('getProblemCodeSystemName')) {
    /**
     * Get a problem code system name from an array of clues.
     *
     * @param array $clues
     *
     * @return string|null
     */
    function getProblemCodeSystemName(array $clues)
    {
        foreach ($clues as $clue) {
            if ('2.16.840.1.113883.6.96' == $clue
                || str_contains(strtolower($clue), ['snomed'])) {
                return Constants::SNOMED_NAME;
            }

            if ('2.16.840.1.113883.6.103' == $clue
                || str_contains(strtolower($clue), ['9'])) {
                return Constants::ICD9_NAME;
            }

            if ('2.16.840.1.113883.6.3' == $clue
                || str_contains(strtolower($clue), ['10'])) {
                return Constants::ICD10_NAME;
            }
        }

        return null;
    }
}

if ( ! function_exists('getProblemCodeSystemCPMId')) {
    /**
     * Get the id of an App\ProblemCodeSystem from an array of clues.
     *
     * @param array $clues
     *
     * @return int|null
     */
    function getProblemCodeSystemCPMId(array $clues)
    {
        $name = getProblemCodeSystemName($clues);

        $map = Constants::CODE_SYSTEM_NAME_ID_MAP;

        if (array_key_exists($name, $map)) {
            return $map[$name];
        }

        return null;
    }
}

if ( ! function_exists('validProblemName')) {
    /**
     * Is the problem name valid.
     *
     * @param $name
     *
     * @return bool
     */
    function validProblemName($name)
    {
        return ! str_contains(
                strtolower($name),
                [
                    'screening',
                    'history',
                    'scan',
                    'immunization',
                    'immunisation',
                    'injection',
                    'vaccine',
                    'vaccination',
                    'vaccin',
                    'screen',
                    'follow up',
                    'followup',
                    'labs',
                    'f/u',
                    'mo fu',
                    'fu on',
                    'fu from',
                    'm fu',
                    'counsel',
                    'adverse effect drug',
                    'counseling',
                    'new pt',
                    'hx',
                    'prediabetes',
                    'check',
                ]
            ) && ! in_array(
                strtolower($name),
                [
                    'fu',
                ]
            );
    }
}

if ( ! function_exists('validAllergyName')) {
    /**
     * Is the allergy name valid.
     *
     * @param $name
     *
     * @return bool
     */
    function validAllergyName($name)
    {
        return ! str_contains(
            strtolower($name),
            [
                'no known',
                'none',
            ]
        );
    }
}

if ( ! function_exists('showDiabetesBanner')) {
    function showDiabetesBanner($patient, $noShow = null)
    {
//        if (!$noShow && $patient
//            && is_a($patient, User::class)
//            && $patient->hasProblem(1)
//            && !$patient->hasProblem(32)
//            && !$patient->hasProblem(33)
//            && $patient->primaryPractice->name != 'northeast-georgia-diagnostic-clinic'
//        ) {
//            return true;
//        }

        return false;
    }
}

if ( ! function_exists('shortenUrl')) {
    /**
     * Create a short URL.
     *
     * @param $url
     *
     * @return string
     * @throws \Waavi\UrlShortener\InvalidResponseException
     *
     */
    function shortenUrl($url)
    {
        return \UrlShortener::driver('bitly-gat')->shorten($url);
    }
}

if ( ! function_exists('validateYYYYMMDDDateString')) {
    /**
     * Validate that the given date string has format YYYY-MM-DD.
     *
     * @param $date
     * @param mixed $throwException
     *
     * @return bool
     * @throws Exception
     *
     * @throws Exception
     */
    function validateYYYYMMDDDateString($date, $throwException = true)
    {
        $isValid = (bool)preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date);

        if ( ! $isValid && $throwException) {
            throw new \Exception('Invalid Date');
        }

        return $isValid;
    }
}

if ( ! function_exists('cast')) {
    /**
     * Cast an object into a different class.
     *
     * Currently this only supports casting DOWN the inheritance chain,
     * that is, an object may only be cast into a class if that class
     * is a descendant of the object's current class.
     *
     * This is mostly to avoid potentially losing data by casting across
     * incompatable classes.
     *
     * @param object $object the object to cast
     * @param string $class the class to cast the object into
     *
     * @return object
     */
    function cast($object, $class)
    {
        if ( ! is_object($object)) {
            throw new InvalidArgumentException('$object must be an object.');
        }
        if ( ! is_string($class)) {
            throw new InvalidArgumentException('$class must be a string.');
        }
        if ( ! class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Unknown class: %s.', $class));
        }
        $ret = app($class);
        foreach (get_object_vars($object) as $key => $value) {
            $ret[$key] = $value;
        }

        return $ret;
    }
}

if ( ! function_exists('is_json')) {
    /**
     * Determine whether the given string is json.
     *
     * @param $string
     *
     * @return bool|null
     *
     * true: the string is valid json
     * null: the string is an empty string, or not a string at all
     * false: the string is invalid json
     */
    function is_json($string)
    {
        if ('' === $string || ! is_string($string)) {
            return null;
        }

        \json_decode($string);
        if (\json_last_error()) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists('read_file_using_generator')) {
    /**
     * Read a file using a generator.
     * https://wiki.php.net/rfc/generators.
     *
     * @param $path
     *
     * @return bool|Generator
     */
    function read_file_using_generator($path)
    {
        if ( ! file_exists($path)) {
            return false;
        }

        $handle = fopen($path, 'r');

        while ( ! feof($handle)) {
            yield fgets($handle);
        }

        fclose($handle);
    }
}
if ( ! function_exists('getEhrReportWritersFolderUrl')) {
    function getEhrReportWritersFolderUrl()
    {
        if (app()->environment(['production', 'worker'])) {
            return 'https://drive.google.com/drive/folders/1NMMNIZKKicOVDNEUjXf6ayAjRbBbFAgh';
        }

        //this is to make local environments faster for devs
        //comment out this if section to use the feature
        if (app()->environment('local')) {
            return null;
        }

        $dir = getGoogleDirectoryByName('ehr-data-from-report-writers');

        if ( ! $dir) {
            return null;
        }

        return "https://drive.google.com/drive/folders/{$dir['path']}";
    }
}

if ( ! function_exists('getGoogleDirectoryByName')) {
    function getGoogleDirectoryByName($name)
    {
        $clh = collect(Storage::drive('google')->listContents('/', true));

        $directory = $clh->where('type', '=', 'dir')
                         ->where('filename', '=', $name)
                         ->first();
        if ( ! $directory) {
            return null;
        }

        return $directory;
    }
}

if ( ! function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['b', 'kb', 'mb', 'gb', 'tb'];

        $bytes = max($bytes, 0);
        $pow   = floor(
            ($bytes
                ? log($bytes)
                : 0) / log(1024)
        );
        $pow   = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if ( ! function_exists('array_keys_exist')) {
    /**
     * Returns TRUE if the given keys are all set in the array. Each key can be any value possible for an array index.
     *
     * @param string[] $keys keys to check
     * @param array $array an array with keys to check
     * @param mixed $missing reference to a variable that that contains the missing keys
     *
     * @return bool true if all given keys exist in the given array, false if not
     * @see array_key_exists()
     *
     */
    function array_keys_exist(array $keys, array $array, &$missing = null)
    {
        $missing = array_diff($keys, array_keys($array));

        return array_reduce(
            $keys,
            function ($carry, $key) use ($array) {
                return $carry && array_key_exists($key, $array);
            },
            true
        );
    }
}

if ( ! function_exists('is_falsey')) {
    function is_falsey($value)
    {
        return is_null($value) || empty($value) || 0 === strcasecmp($value, 'null');
    }
}

if ( ! function_exists('isAllowedToSee2FA')) {
    function isAllowedToSee2FA(User $user = null)
    {
        return (bool)config('auth.two_fa_enabled') && optional($user ?? auth()->user())->isAdmin();
    }
}

if ( ! function_exists('getSampleNotePdfPath')) {
    function getSampleNotePdfPath()
    {
        $path = public_path('assets/pdf/sample-note.pdf');

        if ( ! file_exists($path)) {
            throw new \App\Exceptions\FileNotFoundException();
        }

        return $path;
    }
}

if ( ! function_exists('getSampleCcdaPath')) {
    function getSampleCcdaPath()
    {
        $path = storage_path('ccdas/Samples/demo.xml');

        if ( ! file_exists($path)) {
            throw new \App\Exceptions\FileNotFoundException();
        }

        return $path;
    }
}

if ( ! function_exists('tryDropForeignKey')) {
    function tryDropForeignKey(Blueprint $table, $key)
    {
        try {
            $table->dropForeign($key);
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if (1091 == $errorCode) {
                Log::debug("Key `${key}` does not exist. Nothing to delete." . __FILE__);
            }

            return false;
        }

        return true;
    }
}

if ( ! function_exists('isProductionEnv')) {
    function isProductionEnv()
    {
        return in_array(config('app.env'), ['production', 'worker']);
    }
}

if ( ! function_exists('presentDate')) {
    function presentDate($date, bool $withTime = true)
    {
        if ( ! is_a($date, Carbon::class)) {
            $validator = Validator::make(['date' => $date], ['date' => 'date']);

            if ($validator->fails()) {
                return 'N/A';
            }

            $carbonDate = Carbon::parse($date);
        } else {
            $carbonDate = $date;
        }

        if ($carbonDate->year < 1) {
            return 'N/A';
        }

        return $withTime
            ? $carbonDate->format('Y-m-d h:iA')
            : $carbonDate->format('Y-m-d');
    }
}

if ( ! function_exists('boolValue')) {
    function boolValue($val): bool
    {
        if (is_bool($val)) {
            return boolval($val);
        }
        if (is_string($val)) {
            return in_array($val, ['1', 'true', 'TRUE']);
        }

        return false;
    }
}

if ( ! function_exists('intValue')) {
    function intValue($val, $default = 0): ?int
    {
        if (is_numeric($val)) {
            return intval($val);
        }

        return $default;
    }
}
