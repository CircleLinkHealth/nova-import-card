<?php

namespace App\Repositories;

use App\Contracts\Repositories\InviteRepository;
use App\Entities\Invite;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class InviteRepositoryEloquent
 * @package namespace App\Repositories;
 */
class InviteRepositoryEloquent extends BaseRepository implements InviteRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Invite::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
