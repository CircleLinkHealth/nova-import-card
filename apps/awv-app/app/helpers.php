<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
            return;
        }

        \json_decode($string);
        if (\json_last_error()) {
            return false;
        }

        return true;
    }
}

if ( ! function_exists('sortSurveyQuestions')) {
    /**
     * Sort survey questions using the pivot table, taking into account sub_order.
     */
    function sortSurveyQuestions(Collection $questions): Collection
    {
        $ordering = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => 4,
            'e' => 5,
        ];

        return $questions->sortBy(function ($question) use ($ordering) {
            $subOrder = 0;
            $pivot = $question->pivot;

            if ($pivot->sub_order) {
                $subOrder = is_numeric($pivot->sub_order)
                    ? $pivot->sub_order
                    : $ordering[strtolower($pivot->sub_order)];
            }

            return floatval("{$pivot->order}".'.'."{$subOrder}");
        });
    }
}

if ( ! function_exists('getCpmCallerToken')) {
    /**
     * naive authentication for the CPM Caller Service.
     * @return string
     */
    function getCpmCallerToken()
    {
        return \Hash::make(config('app.key').Carbon::today()->toDateString());
    }
}

if ( ! function_exists('getStringValueFromAnswer')) {
    function getStringValueFromAnswer($val, $default = '')
    {
        if (empty($val)) {
            return $default;
        }

        if (is_string($val)) {
            return $val;
        }

        if (is_array($val)) {
            if (array_key_exists('name', $val)) {
                return getStringValueFromAnswer($val['name']);
            }

            if (array_key_exists('value', $val)) {
                return getStringValueFromAnswer($val['value']);
            }

            return getStringValueFromAnswer($val[0]);
        }

        return $val;
    }
}

if ( ! function_exists('sendSlackMessage')) {
    /**
     * Sends a message to Slack.
     *
     * @param Illuminate\Notifications\Notification $notification - must have a toSlack method
     * @param bool                                  $force        - in case you really want the message to go to slack (testing | debugging)
     */
    function sendSlackMessage(Illuminate\Notifications\Notification $notification, $force = false)
    {
        $isProduction = 'production' === config('app.env');
        if ( ! $force && ! $isProduction) {
            return;
        }
        $endpoint = env('SLACK_DEFAULT_ENDPOINT', null);
        if ( ! $endpoint) {
            return;
        }
        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable();
        $notifiable->route('slack', $endpoint);
        $notifiable->notify($notification);
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

        return '+'.$countryCode.$sanitized;
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

if ( ! function_exists('isSelfEnrollmentTestModeEnabled')) {
    function isSelfEnrollmentTestModeEnabled(): bool
    {
        return filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);
    }
}
