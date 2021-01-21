<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Filters;

use CircleLinkHealth\Core\Filters\QueryFilters;
use Illuminate\Http\Request;

class NurseDailyReportFilters extends QueryFilters
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        $query = $this->request->get('query');

        return json_decode($query, true);
    }

    public function name($name)
    {
        if (empty($name)) {
            return $this->builder;
        }

        $terms = explode(' ', $name);

        foreach ($terms as $term) {
            $this->builder->where(function ($sq) use ($term) {
                $sq->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");
            });
        }

        return $this->builder;
    }
}
