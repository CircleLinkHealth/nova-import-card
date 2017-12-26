<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CPM;

use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CpmProblemUserRepository;

class CpmProblemUserService
{
    private $cpmProblemUserRepo;
    private $userRepo;

    public function __construct(CpmProblemUserRepository $cpmProblemUserRepo, UserRepositoryEloquent $userRepo) {
        $this->cpmProblemUserRepo = $cpmProblemUserRepo;
        $this->userRepo = $userRepo;
    }

    public function repo() {
        return $this->cpmProblemUserRepo;
    }
}
