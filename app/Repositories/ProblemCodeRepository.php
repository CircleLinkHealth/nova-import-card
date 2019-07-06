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
        return ProblemCode::select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function exists($problem_id, $code_system_id)
    {
        return ProblemCode::where([
            'problem_code_system_id' => $code_system_id,
            'problem_id'             => $problem_id,
        ])->exists();
    }

    public function remove($id)
    {
        ProblemCode::where(['id' => $id])->delete();

        return [
            'message' => 'successful',
        ];
    }

    public function service()
    {
        return app(ProblemCodeService::class);
    }
}
