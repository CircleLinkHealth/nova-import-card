<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\ForeignIdRepository;
use App\ForeignId;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class ForeignIdRepositoryEloquent.
 */
class ForeignIdRepositoryEloquent extends BaseRepository implements ForeignIdRepository
{
    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return ForeignId::class;
    }
}
