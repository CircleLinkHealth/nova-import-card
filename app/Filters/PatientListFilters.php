<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use Illuminate\Http\Request;

class PatientListFilters extends QueryFilters
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('name', 'like', '%' . $name . '%');
    }

    public function provider($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('provider_name', 'like', '%' . $name . '%');
    }

    public function hra_status($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        return $this->builder->where('hra_status', '=', $status);
    }

    public function vitals_status($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        return $this->builder->where('vitals_status', '=', $status);
    }

    public function eligibility($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        return $this->builder->where('eligibility', '=', $status);
    }

    public function dob($value)
    {
        if (empty($value)) {
            return $this->builder;
        }

        return $this->builder->where('dob', '=', $value);
    }

    public function globalFilters(): array
    {
        $query = $this->request->get('query');

        $decoded  = json_decode($query, true);
        $filtered = collect($decoded)->filter();

        return $filtered->all();
    }
}
