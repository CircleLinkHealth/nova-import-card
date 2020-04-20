<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class NotesSuccessStories extends Filter
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('success_story', $value);
    }

    /**
     * Defines the default filter value. In this case is where "success_story" = true.
     *
     * @return array|int|mixed
     */
    public function default()
    {
        return 1;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Success Stories' => 1,
        ];
    }
}
