<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\ProblemCode;
use App\Services\ProblemCodeService;
use Illuminate\Support\Facades\DB;

class ProblemCodeRepository
{
    public function count()
    {
        return $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function exists($problem_id, $code_system_id)
    {
        return (bool) $this->model()->where([
            'problem_code_system_id' => $code_system_id,
            'problem_id'             => $problem_id,
        ])->first();
    }

    public function model()
    {
        return app(ProblemCode::class);
    }

    public function remove($id)
    {
        $this->model()->where(['id' => $id])->delete();

        return [
            'message' => 'successful',
        ];
    }

    public function service()
    {
        return app(ProblemCodeService::class);
    }
}
