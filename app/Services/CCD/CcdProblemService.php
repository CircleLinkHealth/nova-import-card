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
            'codes' => $p->codes()->get()
        ];
        return $problem;
    }

    public function problems() {
        $problems = $this->repo()->problems();
        $problems->getCollection()->transform(function ($value) {
            return $this->setupProblem($value);
        });
        return $problems;
    }

    public function problem($id) {
        $problem = $this->repo()->model()->find($id);
        if ($problem) return $this->setupProblem($problem);
        else return null;
    }

    public function getPatientProblems($userId) {
        $user = $this->userRepo->model()->findOrFail($userId);
        
        return $user->ccdProblems()->get()->map([$this, 'setupProblem']);
    }
}
