<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\LocationRepository;
use App\Presenters\LocationPresenter;
use CircleLinkHealth\Customer\Entities\Location;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class LocationRepositoryEloquent.
 */
class LocationRepositoryEloquent extends BaseRepository implements LocationRepository
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
        return Location::class;
    }

    public function presenter()
    {
        return new LocationPresenter();
    }
}
