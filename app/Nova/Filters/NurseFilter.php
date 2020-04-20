<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class NurseFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    public $name      = 'Care Coach';

    /**
     * @var string
     */
    private $column;

    /**
     * NurseFilter constructor.
     */
    public function __construct(string $column = 'id')
    {
        $this->column = $column;
    }

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
        return $query->where($this->column, $value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return User::careCoaches()->pluck('id', 'display_name')->sortKeys()->all();
    }
}
