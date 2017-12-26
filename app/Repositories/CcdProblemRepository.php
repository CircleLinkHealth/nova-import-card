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
use App\Models\CCD\Problem;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\DB;

class CcdProblemRepository extends BaseRepository implements RepositoryInterface
{
    public function model()
    {
        return Problem::class;
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

    public function count() {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->select('name', DB::raw('count(*) as total'))->groupBy('name')->pluck('total')->count();

        $this->resetModel();
        $this->resetScope();

        return $result;
    }
}