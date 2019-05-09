<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\PracticeRepository;
use App\Validators\PracticeValidator;
use CircleLinkHealth\Customer\Entities\Practice;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class PracticeRepositoryEloquent.
 */
class PracticeRepositoryEloquent extends BaseRepository implements PracticeRepository
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
        return Practice::class;
    }

    public function validator()
    {
        return PracticeValidator::class;
    }
}
