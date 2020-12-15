<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Jobs\SendSlackMessage;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

if ( ! function_exists('isProductionEnv')) {
    /**
     * Returns whether or not this is a Production server, ie. used by real users.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isProductionEnv()
    {
        return config('core.is_production_env');
    }
}

if ( ! function_exists('presentDate')) {
    /**
     * Use this function to have a single presentation layer for all user facing dates in CPM.
     *
     * Due to the fact that we don't have a way to sort dates m-d-Y dates in tables yet, we are using $forceHumanForm so that developers can choose when to "force" m-d-Y format.
     *
     * @param $date
     *
     * @return string
     */
    function presentDate($date, bool $withTime = true, bool $withTimezone = false, bool $forceHumanForm = false)
    {
        $dateFormat = 'Y-m-d';
        $timeFormat = $withTimezone
            ? 'h:iA T'
            : 'h:iA';

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

        if ($forceHumanForm) {
            $dateFormat = 'm-d-Y';
        }

        return $withTime
            ? $carbonDate->format("$dateFormat $timeFormat")
            : $carbonDate->format($dateFormat);
    }
}

if ( ! function_exists('isQueueWorkerEnv')) {
    /**
     * Returns whether or not this server runs jobs from the queue.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isQueueWorkerEnv()
    {
        return config('core.is_queue_worker_env');
    }
}

if ( ! function_exists('isUnitTestingEnv')) {
    /**
     * Returns whether or not the test suite is running.
     *
     * @return bool|string
     */
    function isUnitTestingEnv()
    {
        return app()->environment(['testing']);
    }
}

if ( ! function_exists('upg0506IsEnabled')) {
    /**
     * Key: upg0506_is_enabled
     * Default: false.
     */
    function upg0506IsEnabled(): bool
    {
        $key = 'upg0506_is_enabled';
        $val = AppConfig::pull($key, null);
        if (null === $val) {
            return 'true' === AppConfig::set($key, false);
        }

        return 'true' === $val;
    }
}

if ( ! function_exists('getEhrReportWritersFolderUrl')) {
    function getEhrReportWritersFolderUrl()
    {
        //this is to make local environments faster for devs
        //comment out this if section to use the feature
        if (app()->environment('local')) {
            return null;
        }

        $key = 'ehr_report_writers_folder_url';

        return \Cache::remember($key, 2, function () use ($key) {
            return AppConfig::pull($key, null);
        });

//        Commenting out due to Heroku migration
//        $dir = getGoogleDirectoryByName('ehr-data-from-report-writers');
//
//        if ( ! $dir) {
//            return null;
//        }
//
//        return "https://drive.google.com/drive/folders/{$dir['path']}";
    }
}

if ( ! function_exists('isSelfEnrollmentTestModeEnabled')) {
    function isSelfEnrollmentTestModeEnabled(): bool
    {
        return filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);
    }
}

if ( ! function_exists('isAllowedToSee2FA')) {
    function isAllowedToSee2FA(User $user = null)
    {
        $twoFaEnabled = (bool) config('auth.two_fa_enabled');
        if ( ! $twoFaEnabled) {
            return false;
        }

        if ( ! $user) {
            $user = auth()->user();
        }

        if ( ! $user || $user->isParticipant()) {
            return false;
        }

        return $user->isAdmin() || isTwoFaEnabledForPractice($user->program_id);
    }
}

if ( ! function_exists('isTwoFaEnabledForPractice')) {
    /**
     * Key: two_fa_enabled_practices
     * Default: false.
     *
     * @param mixed $practiceId
     */
    function isTwoFaEnabledForPractice($practiceId): bool
    {
        $key = 'two_fa_enabled_practices';
        $val = AppConfig::pull($key, null);
        if (null === $val) {
            AppConfig::set($key, '');

            $twoFaEnabledPractices = [];
        } else {
            $twoFaEnabledPractices = explode(',', $val);
        }

        return in_array($practiceId, $twoFaEnabledPractices);
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
        if (empty($value)) {
            return [];
        }

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

                    return $id[0] ?? null;
                }
            )->values()->toArray();
        }

        if (is_string($value) && Str::contains($value, ',')) {
            return explode(',', $value);
        }

        return array_filter((array) $value);
    }
}

if ( ! function_exists('isCpm')) {
    function isCpm()
    {
        return 'CarePlan Manager' === config('app.name');
    }
}

if ( ! function_exists('usStatesArrayForDropdown')) {
    function usStatesArrayForDropdown(): array
    {
        return [
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        ];
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

if ( ! function_exists('getIpAddress')) {
    /**
     * Get the IP address. This also works with Heroku, where we are behind a load balancer.
     *
     * @return string
     */
    function getIpAddress()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim(end($ipAddresses));
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}

if ( ! function_exists('sendSlackMessage')) {
    /**
     * Sends a message to Slack.
     *
     * @param string $to      - slack channel (should start with '#')
     * @param string $message
     * @param bool   $force   - in case you really want the message to go to slack (testing | debugging)
     */
    function sendSlackMessage($to, $message, $force = false)
    {
        Log::warning("$to: $message");

        if ( ! $force && ! isProductionEnv()) {
            return;
        }

        SendSlackMessage::dispatch($to, $message)->onQueue('default');
    }
}

if ( ! function_exists('validateUsPhoneNumber')) {
    /**
     * @param string
     * @param mixed $phoneNumber
     */
    function validateUsPhoneNumber($phoneNumber): bool
    {
        $validator = \Validator::make(
            [
                'number' => (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumberE164($phoneNumber),
            ],
            [
                'number' => ['required', \Illuminate\Validation\Rule::phone()->country(['US'])],
            ]
        );

        return $validator->passes();
    }
}
