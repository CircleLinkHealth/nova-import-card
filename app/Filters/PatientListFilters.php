<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Filters;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientListFilters extends QueryFilters
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function patient_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('patient_name', 'like', '%' . $name . '%');
    }

    public function provider_name($name)
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

        if ("null" === $status) {
            return $this->builder->whereNull('hra_status');
        }

        return $this->builder->where('hra_status', '=', $status);
    }

    public function vitals_status($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        if ("null" === $status) {
            return $this->builder->whereNull('vitals_status');
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

    public function year($value)
    {
        if (empty($value)) {
            return $this->builder;
        }

        return $this->builder->where('year', '=', $value);
    }

    public function years(array $value)
    {
        if (empty($value)) {
            return $this->builder;
        }

        return $this->builder->whereIn('year', $value);
    }

    public function practiceIds(array $value) {
        if (empty($value)) {
            return $this->builder;
        }

        return $this->builder->whereIn('practice_id', $value);
    }

    public function globalFilters(): array
    {
        $query = $this->request->get('query');

        $decoded  = json_decode($query, true);
        $filtered = collect($decoded)->filter();

        //do not set years if year was set in query
        if ( ! isset($filtered['year'])) {
            $now               = Carbon::now();
            $filtered['years'] = [$now->year - 1, $now->year, $now->year + 1];
        }

        /** @var User $user */
        $user = auth()->user();
        $filtered['practiceIds'] = $user->viewableProgramIds();

        return $filtered->all();
    }
}
