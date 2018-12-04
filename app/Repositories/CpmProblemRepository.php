<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\CpmProblem;

class CpmProblemRepository
{
    public function count()
    {
        return $this->model()->count();
    }

    public function model()
    {
        return app(CpmProblem::class);
    }

    public function noDiabetesFilter()
    {
        return $this->model()->where('name', '!=', 'Diabetes');
    }
}
