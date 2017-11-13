<?php

namespace App\Filters;


use Illuminate\Database\Eloquent\Builder;

class NurseFilters extends QueryFilters
{
    /**
     * Scope for active nurses.
     *
     * @param string $status
     *
     * @return Builder
     */
    public function active($status = 'active')
    {
        return $this->builder->where('status', '=', $status);
    }

    /**
     * Get the states the nurse is licenced in.
     *
     * @return Builder
     */
    public function states($states = null)
    {
        if ( ! $states) {
            return $this->builder->with('states');
        }

        if (!is_array($states)) {
            $states = [$states];
        }

        $this->builder->whereHas('states', function ($q) use ($states) {
            $q->whereIn('code', $states);
        })->with('states');
    }


}