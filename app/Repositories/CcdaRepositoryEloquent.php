<?php

namespace App\Repositories;

use App\Models\CCD\Ccda;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\CcdaRepository;
use App\Validators\CcdaValidator;

/**
 * Class CcdaRepositoryEloquent
 * @package namespace App\Repositories;
 */
class CcdaRepositoryEloquent extends BaseRepository implements CcdaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Ccda::class;
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
