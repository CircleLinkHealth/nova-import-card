<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\CcmTimeApiLog;
use App\Contracts\Repositories\CcmTimeApiLogRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class CcmTimeApiLogRepositoryEloquent.
 */
class CcmTimeApiLogRepositoryEloquent extends BaseRepository implements CcmTimeApiLogRepository
{
    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Log Activities already sent to Aprima so that we'll know not to send them again.
     *
     * @param array $attributes
     * @param array $values
     *
     * @return mixed
     */
    public function logSentActivity(array $attributes, array $values = [])
    {
        return $this->makeModel()->updateOrCreate($attributes, $values);
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return CcmTimeApiLog::class;
    }
}
