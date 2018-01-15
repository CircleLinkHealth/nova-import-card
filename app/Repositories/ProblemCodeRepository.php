<?php

namespace App\Repositories;

use App\ProblemCode;
use Illuminate\Support\Facades\DB;

class ProblemCodeRepository
{
    public function model()
    {
        return app(ProblemCode::class);
    }

    public function count() {
        return $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }
}