<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\InviteRepository;
use CircleLinkHealth\Customer\Entities\Invite;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class InviteRepositoryEloquent.
 */
class InviteRepositoryEloquent extends BaseRepository implements InviteRepository
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
        return Invite::class;
    }
}
