<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class MonthFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        // see usage in PamEndOfMonthReport.php
        return $query;
    }

    public function default()
    {
        return now()->toDateString();
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        $result         = [];
        $previousMonths = 6;
        for ($i = 0; $i < $previousMonths; ++$i) {
            $month        = now()->subMonths($i);
            $key          = $month->monthName.' '.$month->year;
            $result[$key] = $month->toDateString();
        }

        return $result;
    }
}
