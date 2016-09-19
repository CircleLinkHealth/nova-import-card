<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProgramRepository;
use App\Program;
use App\Validators\ProgramValidator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class ProgramRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ProgramRepositoryEloquent extends BaseRepository implements ProgramRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Program::class;
    }

    public function validator()
    {
        return ProgramValidator::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
