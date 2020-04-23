<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;

class OnOrAfterDateFilter extends DateFilter
{
    public $name = 'Created on or after';
    /**
     * The column that should be filtered on.
     *
     * @var string
     */
    protected $column;

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct(string $column)
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
        $date = $value;

        if (is_array($value) && array_key_exists('date', $value)) {
            $date = $value['date'];
        }

        return $query->where($this->column, '>', Carbon::parse($date));
    }

    public function default()
    {
        return now()->subWeeks(2)->toDateString();
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key()
    {
        return 'created_on_or_after_'.$this->column;
    }
}
