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
