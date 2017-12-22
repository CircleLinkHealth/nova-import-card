<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;


use App\User;
use App\Patient;
use App\Models\CPM\CpmProblem;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CpmProblemRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return CpmProblem::class;
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
        return null;
    }
}