<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;

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

if ( ! function_exists('sortSurveyQuestions')) {
    /**
     * Sort survey questions using the pivot table, taking into account sub_order.
     *
     * @param \Illuminate\Support\Collection $questions
     *
     * @return \Illuminate\Support\Collection
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
            $pivot    = $question->pivot;

            if ($pivot->sub_order) {
                $subOrder = is_numeric($pivot->sub_order)
                    ? $pivot->sub_order
                    : $ordering[strtolower($pivot->sub_order)];
            }

            return floatval("{$pivot->order}" . "." . "{$subOrder}");
        });
    }
}

if ( ! function_exists('getCpmCallerToken')) {

    /**
     * naive authentication for the CPM Caller Service
     * @return string
     */
    function getCpmCallerToken()
    {
        return \Hash::make(config('app.key') . Carbon::today()->toDateString());
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
     * @param bool $force - in case you really want the message to go to slack (testing | debugging)
     */
    function sendSlackMessage(Illuminate\Notifications\Notification  $notification, $force = false)
    {
        $isProduction = config('app.env') === 'production';
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
