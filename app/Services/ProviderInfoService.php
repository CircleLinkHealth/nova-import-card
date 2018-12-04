<?php namespace App\Services;

use App\Repositories\ProviderInfoRepository;
use App\Repositories\UserRepositoryEloquent;

class ProviderInfoService
{
    private $providerInfoRepo;
    private $userRepo;

    public function __construct(ProviderInfoRepository $providerInfoRepo, UserRepositoryEloquent $userRepo)
    {
        $this->providerInfoRepo = $providerInfoRepo;
        $this->userRepo = $userRepo;
    }

    public function repo()
    {
        return $this->providerInfoRepo;
    }

    public function providers()
    {
        return $this->repo()->providers();
    }

    public function getPatientProviders($userId)
    {
        $user = $this->userRepo->user($userId);
        return $user->practices()->get()->map(function ($p) {
            return $p->providers();
        })->reduce(function ($arr, $item) {
            return $arr->concat($item);
        }, collect([]))->groupBy('id')->map(function ($u) {
            return $u->first();
        })->values()->map(function ($u) {
            return $u->safe();
        });
    }
}
