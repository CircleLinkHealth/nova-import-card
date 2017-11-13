<?php

namespace App\Filters;


use Illuminate\Database\Eloquent\Builder;

class NurseFilters extends QueryFilters
{
    public function globalFilters()
    {
        return [
            'active' => ''
        ];
    }

    /**
     * Scope for active nurses.
     *
     * @param string $status
     *
     * @return Builder
     */
    public function active($status = 'active')
    {
        return $this->status($status);
    }

    /**
     * Scope nurses by status.
     *
     * @param string $status
     *
     * @return Builder
     */
    public function status($status = 'active')
    {
        return $this->builder->where('status', '=', $status);
    }

    /**
     * Get the states the nurse is licenced in.
     * By default the and operator is selected, which menas that only nurses that include all states will be included.
     *
     * @param string $states Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function states($states = null, $operator = 'and')
    {
        if ( ! $states) {
            return $this->builder->with('states');
        }

        if (str_contains($states, ',')) {
            $states = explode(',', $states);
        }

        if ( ! is_array($states)) {
            $states = [$states];
        }

        if ($operator == 'and') {
            foreach ($states as $state) {
                $this->builder->whereHas('states', function ($q) use ($state) {
                    $q->where('code', $state);
                });
            }
        }

        if ($operator == 'or') {
            $this->builder->whereHas('states', function ($q) use ($states) {
                $q->whereIn('code', $states);
            });
        }

        $this->builder->with('states');
    }

    /**
     * Get nurses that are licenced in any of the states provided.
     *
     * @param string $states Comma delimited State Codes. Example: 'NJ, NY, GA'
     * @param string $operator Can 'and' or 'or'
     *
     * @return Builder
     */
    public function statesOr($states = null, $operator = 'or')
    {
        return $this->states($states, $operator);
    }

    public function windows()
    {
        return $this->builder->with('windows');
    }

}