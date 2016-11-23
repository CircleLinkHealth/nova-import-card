<?php

namespace App\Repositories;

use App\Contracts\Repositories\PracticeRepository;
use App\Practice;
use App\Validators\PracticeValidator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class PracticeRepositoryEloquent
 * @package namespace App\Repositories;
 */
class PracticeRepositoryEloquent extends BaseRepository implements PracticeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Practice::class;
    }

    public function validator()
    {
        return PracticeValidator::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
