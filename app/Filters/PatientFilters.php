<?php

namespace App\Filters;

use App\Repositories\PatientRepository;
use Illuminate\Http\Request;

class PatientFilters extends QueryFilters
{
    protected $request;

    public function __construct(Request $request, PatientRepository $patientRepository)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function globalFilters(): array
    {
        return [];
    }
}