<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UnresolvedCallbacksFilter extends Filter
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
        return $query->where('date', '>=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        $today = Carbon::now()->startOfDay();

        return [
            'Today'        => $today,
            '2 days ago'   => $today->copy()->subDays(2),
            '4 days ago'   => $today->copy()->subDays(4),
            '1 week ago'   => $today->copy()->subDays(7),
            '2 weeks ago'  => $today->copy()->subDays(14),
            'This month'   => $today->copy()->startOfMonth(),
            '1 month ago'  => $today->copy()->startOfMonth()->subMonth(),
            '3 months ago' => $today->copy()->startOfMonth()->subMonths(2),
            '6 months ago' => $today->copy()->startOfMonth()->subMonths(5),
            'This year'    => $today->copy()->startOfYear(),
        ];
    }
}
