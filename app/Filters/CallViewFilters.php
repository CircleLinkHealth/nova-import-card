<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 12/09/2018
 * Time: 15:42
 */

namespace App\Filters;


use Illuminate\Http\Request;

class CallViewFilters extends QueryFilters
{

    /**
     * CallViewFilters constructor.
     * Sorting and filters just work, simply because
     * the column names match the view table names and they
     * are ordered by them.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function sort_lastCallStatus($term) {
        return $this->builder->orderBy('no_call_attempts_since_last_success', $term);
    }

    public function globalFilters(): array
    {
        return [];
    }
}