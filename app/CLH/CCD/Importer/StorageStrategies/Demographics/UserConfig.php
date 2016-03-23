<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Demographics;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CLH\Repositories\UserRepository;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserConfig extends BaseStorageStrategy implements StorageStrategy
{
    public function import($userConfig)
    {
        $userRepo = new UserRepository();
        $userRepo->updateUserConfig($this->user, new ParameterBag($userConfig));
    }
}