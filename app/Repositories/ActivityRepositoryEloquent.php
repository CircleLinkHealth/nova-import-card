<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\ActivityRepository;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class ActivityRepositoryEloquent.
 */
class ActivityRepositoryEloquent extends BaseRepository implements ActivityRepository
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
        return Activity::class;
    }
}
