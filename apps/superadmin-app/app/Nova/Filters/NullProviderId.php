<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class NullProviderId extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'boolean-filter';
    public $name      = 'Without matching Provider';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $value['enabled'] ? $query->whereNull('provider_id') : $query;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Enabled' => 'enabled',
        ];
    }
}
