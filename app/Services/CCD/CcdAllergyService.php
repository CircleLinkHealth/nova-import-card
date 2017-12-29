<?php

namespace App\Services\CCD;

use App\User;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\CcdAllergyRepository;

class CcdProblemService
{
    private $allergyRepo;
    private $userRepo;

    public function __construct(CcdAllergyRepository $allergyRepo, UserRepositoryEloquent $userRepo) {
        $this->allergyRepo = $allergyRepo;
        $this->userRepo = $userRepo;
    }

    public function repo() {
        return $this->allergyRepo;
    }
}
