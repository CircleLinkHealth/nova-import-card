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
        return [
            'id'    => $p->id,
            'name'  => $p->name,
            'cpm_id'  => $p->cpm_problem_id,
            'patients' => Problem::where('name', $p->name)->get([ 'patient_id' ])->map(function ($item) {
                return $item->patient_id;
            })
        ];
    }
}
