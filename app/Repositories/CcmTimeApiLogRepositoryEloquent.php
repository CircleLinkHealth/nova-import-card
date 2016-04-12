<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\CcmTimeApiLogRepository;
use App\CcmTimeApiLog;
use App\Validators\CcmTimeApiLogValidator;

/**
 * Class CcmTimeApiLogRepositoryEloquent
 * @package namespace App\Repositories;
 */
class CcmTimeApiLogRepositoryEloquent extends BaseRepository implements CcmTimeApiLogRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return CcmTimeApiLog::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function logSentActivity(array $attributes, array $values = array())
    {
        return $this->makeModel()->updateOrCreate($attributes, $values);
    }

}
