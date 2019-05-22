<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Repositories\ProviderInfoRepository;
use App\Repositories\UserRepositoryEloquent;
use CircleLinkHealth\Customer\Entities\User;

class ProviderInfoService
{
    private $providerInfoRepo;
    private $userRepo;

    public function __construct(ProviderInfoRepository $providerInfoRepo, UserRepositoryEloquent $userRepo)
    {
        $this->providerInfoRepo = $providerInfoRepo;
        $this->userRepo         = $userRepo;
    }

    public function getPatientProviders($userId)
    {
        $user = $this->userRepo->user($userId);

        return User::ofType('provider')
            ->intersectPracticesWith($user)
            ->get()
            ->transform(
                function ($u) {
                    return $u->safe();
                }
                   );
    }

    public function providers()
    {
        return $this->repo()->providers();
    }

    public function repo()
    {
        return $this->providerInfoRepo;
    }
}
