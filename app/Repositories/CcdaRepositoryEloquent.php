<?php

namespace App\Repositories;

use App\Contracts\Repositories\CcdaRepository;
use App\Models\MedicalRecords\Ccda;
use App\Validators\CcdaValidator;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

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

    public function logSentActivity(array $attributes, array $values = [])
    {
        // TODO: Implement logSentActivity() method.
    }
}
