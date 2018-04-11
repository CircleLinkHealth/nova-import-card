<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Contracts\Repositories\CcdaRequestRepository;
use App\Entities\CcdaRequest;
use App\Validators\CcdaRequestValidator;

/**
 * Class CcdaRequestRepositoryEloquent
 * @package namespace App\Repositories;
 */
class CcdaRequestRepositoryEloquent extends BaseRepository implements CcdaRequestRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return CcdaRequest::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    public function validator()
    {
        return CcdaRequestValidator::class;
    }


    public function create(array $attributes)
    {
        try {
            $created = parent::create($attributes);
        } catch (\Exception $e) {
            if ($e->errorInfo[1] && str_contains($e->errorInfo[2], 'ccda_requests_vendor_patient_id_unique')) {
                //if it's duplicate unique key exception for ccda_requests_vendor_patient_id_unique
                return false;
            }
        }

        return $created;
    }
}
