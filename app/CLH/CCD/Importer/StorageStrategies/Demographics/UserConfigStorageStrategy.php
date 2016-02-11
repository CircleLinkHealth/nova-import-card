<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Demographics;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CLH\Repositories\WpUserRepository as UserRepository;
use App\WpUser as User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserConfigStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($userConfig)
    {
        $userRepo = new UserRepository();
        $user = User::find($this->userId);
        $userRepo->updateUserConfig($user, new ParameterBag($userConfig->getArray()));
    }
}