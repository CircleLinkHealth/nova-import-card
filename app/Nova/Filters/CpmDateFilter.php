<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\DateFilter;

class CpmDateFilter extends DateFilter
{
    public $name = 'Created on or after';
    /**
     * The column that should be filtered on.
     *
     * @var string
     */
    protected $column;
    protected $defaultDate;
    protected $operator = '>';

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

        return $query->where($this->column, $this->operator, Carbon::parse($date));
    }

    public function default()
    {
        return $this->defaultDate;
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key()
    {
        return Str::slug($this->name).'_'.$this->column;
    }

    /**
     * @param null $defaultDate
     *
     * @return CpmDateFilter
     */
    public function setDefaultDate($defaultDate)
    {
        $this->defaultDate = $defaultDate;

        return $this;
    }

    public function setName(string $name): CpmDateFilter
    {
        $this->name = $name;

        return $this;
    }

    public function setOperator(string $operator): CpmDateFilter
    {
        $this->operator = $operator;

        return $this;
    }
}
