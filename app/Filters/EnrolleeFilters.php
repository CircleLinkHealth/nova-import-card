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

        $decoded = json_decode($query, true);
        $filtered = collect($decoded)->filter();
        return $filtered->all();
    }

    public function provider_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('provider_id', 'like', '%' . $id . '%');
    }

    public function practice_id($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('practice_id', 'like', '%' . $id . '%');
    }

    public function primary_insurance($id)
    {
        if (empty($id)) {
            return $this->builder;
        }

        return $this->builder->where('primary_insurance', 'like', '%' . $id . '%');
    }
}