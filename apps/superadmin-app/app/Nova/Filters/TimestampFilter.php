<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\DateFilter;

class TimestampFilter extends DateFilter
{
    /**
     * The column that should be filtered on.
     *
     * @var string
     */
    protected $column;

    /**
     * @var Carbon
     */
    private $default;

    /**
     * @var string 'from' | 'to'
     */
    private $type;

    /**
     * Create a new filter instance.
     *
     * @param $name
     * @param string $column
     * @param string $type    Either 'from' or 'to'
     * @param Carbon $default
     */
    public function __construct($name, $column, $type, Carbon $default = null)
    {
        $this->name    = $name;
        $this->column  = $column;
        $this->type    = $type;
        $this->default = $default;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where($this->column, 'from' === $this->type ? '>=' : '<', Carbon::parse($value));
    }

    /**
     * @return array|Carbon|mixed
     */
    public function default()
    {
        return ($this->default)->toDateTimeString() ?? parent::default();
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key()
    {
        return 'timestamp_'.$this->column.$this->type;
    }
}
