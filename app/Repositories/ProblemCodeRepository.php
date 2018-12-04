<?php

namespace App\Repositories;

use App\Models\ProblemCode;
use App\Services\ProblemCodeService;
use Illuminate\Support\Facades\DB;

class ProblemCodeRepository
{
    public function model()
    {
        return app(ProblemCode::class);
    }

    public function count()
    {
        return $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();
    }

    public function service()
    {
        return app(ProblemCodeService::class);
    }

    public function exists($problem_id, $code_system_id)
    {
        return !!$this->model()->where([
            'problem_code_system_id' => $code_system_id,
            'problem_id' => $problem_id
        ])->first();
    }
    
    public function remove($id)
    {
        $this->model()->where([ 'id' => $id ])->delete();
        return [
            'message' => 'successful'
        ];
    }
}
