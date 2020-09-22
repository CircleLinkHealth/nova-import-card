<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Filters;

use CircleLinkHealth\Core\Filters\QueryFilters;
use Illuminate\Http\Request;

class UserFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        return [];
    }
}
