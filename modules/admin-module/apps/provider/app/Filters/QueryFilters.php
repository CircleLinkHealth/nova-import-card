<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilters
{
    /**
     * The builder instance.
     *
     * @var Builder
     */
    protected $builder;
    /**
     * The request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Create a new QueryFilters instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply the filters to the builder.
     *
     * @return Builder
     */
    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach (array_merge($this->filters(), $this->globalFilters()) as $name => $value) {
            if ( ! method_exists($this, $name)) {
                continue;
            }
            if (isset($value)) {
                $this->$name($value);
            } else {
                $this->$name();
            }
        }

        return $this->builder;
    }

    /**
     * Get all request filters data.
     *
     * @return array
     */
    public function filters()
    {
        return $this->request->all();
    }

    /*
     * Returns an array of Global Filters
     *
     * @return array
     */
    abstract public function globalFilters(): array;
}
