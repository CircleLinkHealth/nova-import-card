<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Contracts\Repositories\CcdaRepository;
use App\Models\MedicalRecords\Ccda;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class CcdaRepositoryEloquent.
 */
class CcdaRepositoryEloquent extends BaseRepository implements CcdaRepository
{
    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function logSentActivity(array $attributes, array $values = [])
    {
        // TODO: Implement logSentActivity() method.
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Ccda::class;
    }
}
