<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 12/12/2018
 * Time: 7:08 PM
 */

namespace App\Filters;

use Illuminate\Http\Request;

class EnrolleeFilters extends QueryFilters
{

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        $query = $this->request->get('query');

        $decoded               = json_decode($query, true);
        $decoded['hideStatus'] = array_merge($decoded['hideStatus'], [
            'legacy',
            'rejected',
        ] );

        $filtered              = collect($decoded)->filter();

        return $filtered->all();
    }

    public function mrn($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('mrn', 'like', '%' . $id . '%');
    }

    public function first_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('first_name', 'like', '%' . $name . '%');
    }

    public function last_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('last_name', 'like', '%' . $name . '%');
    }

    public function provider_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('provider_name', 'like', '%' . $name . '%');
    }

    public function lang($lang)
    {
        if (empty($lang)) {
            return $this->builder;
        }

        return $this->builder->where('lang', 'like', '%' . $lang . '%');
    }

    public function practice_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('practice_name', 'like', '%' . $name . '%');
    }

    public function care_ambassador_name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        return $this->builder->where('care_ambassador_name', 'like', '%' . $name . '%');
    }

    public function status($status)
    {
        if (empty($status)) {
            return $this->builder;
        }

        return $this->builder->where('status', 'like', '%' . $status . '%');
    }


    public function primary_insurance($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('primary_insurance', 'like', '%' . $id . '%');
    }

    public function secondary_insurance($insurance)
    {
        if (empty($insurance)) {
            return $this->builder;
        }

        return $this->builder->where('secondary_insurance', 'like', '%' . $insurance . '%');
    }

    public function tertiary_insurance($insurance)
    {
        if (empty($insurance)) {
            return $this->builder;
        }

        return $this->builder->where('tertiary_insurance', 'like', '%' . $insurance . '%');
    }

    public function eligibility_job_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('eligibility_job_id', 'like', '%' . $id . '%');
    }

    public function medical_record_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('medical_record_id', 'like', '%' . $id . '%');
    }

    public function attempt_count($count){

        if (empty($count)) {
            return $this->builder;
        }

        return $this->builder->where('attempt_count', 'like', '%' . $count . '%');
    }

    public function hideStatus($statuses){
            return $this->builder->whereNotIn('status', $statuses);
    }

    public function hideAssigned($hideAssigned)
    {
        if ($hideAssigned) {
            return $this->builder->where('care_ambassador_name', '=', null);
        }

        return $this->builder;
    }
}