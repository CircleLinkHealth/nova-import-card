<?php


use App\AppConfig;
use App\CarePlanTemplate;
use App\Constants;
use App\Jobs\SendSlackMessage;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

if (!function_exists('parseIds')) {
    /**
     * Get all of the IDs from the given mixed value.
     *
     * @param  mixed  $value
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
            return $value->map(function($el){
                $id = parseIds($el);
                return $id[0];
            })->values()->toArray();
        }

        if (is_string($value) && str_contains($value, ',')) {
            return explode(',', $value);
        }

        return array_filter((array) $value);
    }
}

if (!function_exists('str_substr_after')) {
    /**
     * Get the substring after the given character
     *
     * @param $string
     * @param string $character
     *
     * @return string
     */
    function str_substr_after($string, $character = '/')
    {
        $pos = strrpos($string, $character);

        return $pos === false ? $string : substr($string, $pos + 1);
    }
}

if (!function_exists('activeNurseNames')) {
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


if (!function_exists('sendSlackMessage')) {
    /**
     * Sends a message to Slack
     *
     * @param $to
     * @param $message
     *
     *
     */
    function sendSlackMessage($to, $message)
    {
        if (!in_array(app()->environment(), ['production', 'worker'])) {
            return;
        }

        $job = new SendSlackMessage($to, $message);

        dispatch($job);
    }
}

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

if (!function_exists('detectDelimiter')) {
    function detectDelimiter($fileHandle, $length)
    {
        $delimiters = ["\t", ";", "|", ","];
        $data_1 = $data_2 = $delimiter = null;

        foreach ($delimiters as $d) {
            $data_1 = fgetcsv($fileHandle, $length, $d);
            if (sizeof($data_1) > sizeof($data_2)) {
                $delimiter = sizeof($data_1) > sizeof($data_2)
                    ? $d
                    : $delimiter;
                $data_2 = $data_1;
            }
            rewind($fileHandle);
        }

        return $delimiter;
    }
}

if (!function_exists('parseCsvToArray')) {
    /**
     * Parses a CSV file into an array.
     *
     * @param $file
     *
     * @return array
     */
    function parseCsvToArray($file, $length = 0, $delimiter = null)
    {
        $csvArray = $fields = [];
        $i = 0;
        $handle = @fopen($file, "r");
        $delimiter = $delimiter ?? detectDelimiter($handle, $length = 0);

        if ($handle) {
            while (($row = fgetcsv($handle, $length, $delimiter)) !== false) {
                if (empty($fields)) {
                    $row = array_map('strtolower', $row);

                    $row = array_map(function ($string) {
                        return str_replace(' ', '_', $string);
                    }, $row);

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
        $getHours = sprintf('%02d', floor($seconds / 3600));
        $getMins = sprintf('%02d', floor(($seconds - ($getHours * 3600)) / 60));

        return $getHours . ':' . $getMins;
    }
}

if (!function_exists('secondsToMMSS')) {
    function secondsToMMSS($seconds)
    {
        $minutes = sprintf('%02d', floor($seconds / 60));
        $seconds = sprintf(':%02d', (int)$seconds % 60);

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

if (!function_exists('carbonGetNext')) {
    /**
     * Get carbon instance of the next $day
     *
     * @param $day
     *
     * @return Carbon|false
     */
    function carbonGetNext($day = 'monday')
    {
        if (!is_numeric($day)) {
            $dayOfWeek = clhToCarbonDayOfWeek(dayNameToClhDayOfWeek($day));
            $dayName = $day;
        }

        if (is_numeric($day)) {
            $dayOfWeek = clhToCarbonDayOfWeek($day);
            $dayName = clhDayOfWeekToDayName($day);
        }

        if (!isset($dayOfWeek)) {
            return false;
        }

        $now = Carbon::now();

        if ($now->dayOfWeek == $dayOfWeek) {
            return $now;
        }

        return $now->parse("next $dayName");
    }
}

if (!function_exists('clhToCarbonDayOfWeek')) {
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
        return $dayOfWeek == 7
            ? 0
            : $dayOfWeek;
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
     * @param $clhDayOfWeek
     *
     * @return int
     *
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

if (!function_exists('dayNameToClhDayOfWeek')) {
    /**
     * Convert a day such as Monday, Tuesday to CLH DayOfWeek (1,2,3,4,5,6,7)
     *
     * @param $clhDayOfWeek
     *
     * @return int
     *
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

if (!function_exists('weekDays')) {
    /**
     * Returns the days of the week
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
    function generateRandomString(
        $l,
        $c = 'abcdefghijklmnopqrstuvwxyz1234567890'
    ) {
        for ($s = '', $cl = strlen($c) - 1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i) {
            ;
        }

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


if (!function_exists('defaultCarePlanTemplate')) {
    /**
     * Returns CircleLink's default CarePlanTemplate
     *
     * @return CarePlanTemplate
     */
    function getDefaultCarePlanTemplate(): CarePlanTemplate
    {
        $id = getAppConfig('default_care_plan_template_id');

        return CarePlanTemplate::find($id);
    }
}

if (!function_exists('getAppConfig')) {
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

if (!function_exists('setAppConfig')) {
    /**
     * Save an AppConfig key, value and then return it.
     *
     * @param string $key
     *
     * @return CarePlanTemplate
     */
    function setAppConfig(string $key, $value)
    {
        $conf = AppConfig::updateOrCreate([
            'config_key' => $key,
        ], [
            'config_value' => $value,
        ]);

        return $conf
            ? $conf->config_value
            : null;
    }
}


if (!function_exists('snakeToSentenceCase')) {
    /**
     * Convert Snake to Sentence Case
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

if (!function_exists('linkToDownloadFile')) {
    /**
     * Generate a file to download a file
     *
     * @param $path
     *
     * @return string
     * @throws Exception
     */
    function linkToDownloadFile($path, $absolute = false)
    {
        if (!$path) {
            throw new \Exception("File path cannot be empty");
        }

        return route('download', [
            'filePath' => base64_encode($path),
        ], $absolute);
    }
}

if (!function_exists('linkToCachedView')) {
    /**
     * Generate a link to a cached view
     *
     * @param $viewHashKey
     *
     * @return string
     * @throws Exception
     *
     */
    function linkToCachedView($viewHashKey, $absolute = false)
    {
        if (!$viewHashKey) {
            throw new \Exception("File path cannot be empty");
        }

        return route('get.cached.view.by.key', ['key' => $viewHashKey], $absolute);
    }
}

if (!function_exists('parseCallDays')) {
    function parseCallDays($preferredCallDays)
    {
        if (!$preferredCallDays || str_contains(strtolower($preferredCallDays), ['any'])) {
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
            $to = array_search($exploded[1], weekDays());

            for ($i = $from; $i <= $to; $i++) {
                $days[] = $i;
            }
        } else {
            $days[] = dayNameToClhDayOfWeek($preferredCallDays);
        }

        return array_filter($days);
    }
}

if (!function_exists('parseCallTimes')) {
    function parseCallTimes($preferredCallTimes)
    {
        if (!$preferredCallTimes) {
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
            $times['end'] = Carbon::parse(trim($preferredTimes[1]))->toTimeString();
        } else {
            $times = [
                'start' => '09:00:00',
                'end'   => '17:00:00',
            ];
        }

        return $times;
    }
}


if (!function_exists('getProblemCodeSystemName')) {
    /**
     * Get a problem code system name from an array of clues
     *
     * @param array $clues
     *
     * @return null|string
     */
    function getProblemCodeSystemName(array $clues)
    {
        foreach ($clues as $clue) {
            if ($clue == '2.16.840.1.113883.6.96'
                || str_contains(strtolower($clue), ['snomed'])) {
                return Constants::SNOMED_NAME;
            }

            if ($clue == '2.16.840.1.113883.6.103'
                || str_contains(strtolower($clue), ['9'])) {
                return Constants::ICD9_NAME;
            }

            if ($clue == '2.16.840.1.113883.6.3'
                || str_contains(strtolower($clue), ['10'])) {
                return Constants::ICD10_NAME;
            }
        }

        return null;
    }
}

if (!function_exists('getProblemCodeSystemCPMId')) {
    /**
     * Get the id of an App\ProblemCodeSystem from an array of clues
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

if (!function_exists('validProblemName')) {
    /**
     * Is the problem name valid
     *
     * @param $name
     *
     * @return boolean
     */
    function validProblemName($name)
    {
        return ! str_contains(strtolower($name), [
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
        ]);
    }
}

if (!function_exists('showDiabetesBanner')) {
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

if (!function_exists('shortenUrl')){
    /**
     * Create a short URL
     *
     * @param $url
     *
     * @return string
     * @throws \Waavi\UrlShortener\InvalidResponseException
     */
    function shortenUrl($url){
        $shortUrl = \UrlShortener::driver('bitly-gat')->shorten($url);
        return $shortUrl;
    }
}

if (!function_exists('validateYYYYMMDDDateString')){
    /**
     * Validate that the given date string has format YYYY-MM-DD
     *
     * @param $date
     *
     * @return bool
     * @throws Exception
     */
    function validateYYYYMMDDDateString($date, $throwException = true){
        $isValid = (bool) preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date);

        if (!$isValid && $throwException) {
            throw new \Exception("Invalid Date");
        }

        return $isValid;
    }
}

if (!function_exists('cast')) {
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
    * @param object $object The object to cast.
    * @param string $class The class to cast the object into.
    * @return object
    */
    function cast($object, $class) {
        if( !is_object($object) ) 
            throw new InvalidArgumentException('$object must be an object.');
        if( !is_string($class) )
            throw new InvalidArgumentException('$class must be a string.');
        if( !class_exists($class) )
            throw new InvalidArgumentException(sprintf('Unknown class: %s.', $class));
        $ret = app($class);
        foreach (get_object_vars($object) as $key => $value) {
            $ret[$key] = $value;
        }
        return $ret;
    }
}

if (!function_exists('is_json')) {
    /**
     * Determine whether the given string is json
     *
     * @param $string
     *
     * @return bool|null
     *
     * true: the string is valid json
     * null: the string is an empty string, or not a string at all
     * false: the string is invalid json
     */
    function is_json($string) {
        if ($string === '' || !is_string($string)) {
            return null;
        }

        \json_decode($string);
        if (\json_last_error()) {
            return false;
        }

        return true;
    }
}
