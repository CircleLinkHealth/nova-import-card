<?php namespace App\Services;

use App\Repositories\ProviderInfoRepository;

class ProviderInfoService
{
    private $providerInfoRepo;

    public function __construct(ProviderInfoRepository $providerInfoRepo) {
        $this->providerInfoRepo = $providerInfoRepo;
    }

    public function repo() {
        return $this->providerInfoRepo;
    }

    public function providers() {
        return $this->repo()->providers();
    }
}
