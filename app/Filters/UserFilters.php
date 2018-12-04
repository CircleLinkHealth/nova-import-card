<?php

namespace App\Filters;

use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
