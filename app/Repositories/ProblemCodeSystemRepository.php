<?php

namespace App\Repositories;

use App\ProblemCodeSystem;
use Illuminate\Support\Facades\DB;

class ProblemCodeSystemRepository
{
    public function model()
    {
        return app(ProblemCodeSystem::class);
    }

    public function count()
    {
        return $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }
}
