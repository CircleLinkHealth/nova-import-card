<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CCD;

use App\User;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CcdProblemRepository;
use App\Repositories\Criteria\CriteriaFactory;

class CcdProblemService
{
    private $problemRepo;
    private $userRepo;

    public function __construct(CcdProblemRepository $problemRepo, UserRepositoryEloquent $userRepo) {
        $this->problemRepo = $problemRepo;
        $this->userRepo = $userRepo;
    }

    public function repo() {
        return $this->problemRepo;
    }
        
    function setupProblem($p) {
        $problem = [
            'id'    => $p->id,
            'name'  => $p->name,
            'cpm_id'  => $p->cpm_problem_id,
            'patients' => array_values(array_unique(array_map(function ($problem) {
                                return $problem->patient_id;
                            }, $this->repo()->findWhere(['name' => $p->name ])->all(['patient_id']))))
        ];
        return $problem;
    }

    function problems() {
        $groupByNameCriteria = new CriteriaFactory(function ($model) {
            return $model->groupBy('name')->orderBy('id');
        });
        $this->repo()->pushCriteria($groupByNameCriteria);
        $problems = $this->repo()->paginate(30);
        $this->repo()->popCriteria($groupByNameCriteria);
        
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });
        return $problems;
    }
}
