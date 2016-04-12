<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\ForeignIdRepository;
use App\ForeignId;
use App\Validators\ForeignIdValidator;

/**
 * Class ForeignIdRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ForeignIdRepositoryEloquent extends BaseRepository implements ForeignIdRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ForeignId::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function logSentActivity(array $attributes, array $values = array())
    {
        // TODO: Implement logSentActivity() method.
    }
}
