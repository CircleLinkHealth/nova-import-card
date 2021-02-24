<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ChargeableMonth extends Filter
{
    const DEFAULT_MONTHS_TO_GO_BACK_T0 = 6;
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
        return $query->where('chargeable_month', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        $result = [];
        for ($i = 0; $i < self::DEFAULT_MONTHS_TO_GO_BACK_T0; ++$i) {
            $month        = now()->subMonths($i);
            $key          = $month->monthName.' '.$month->year;
            $result[$key] = $month->startOfMonth()->toDateString();
        }

        return $result;
    }
}
