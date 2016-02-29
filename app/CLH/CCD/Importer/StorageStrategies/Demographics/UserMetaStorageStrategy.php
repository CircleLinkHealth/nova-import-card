<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Demographics;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CLH\Repositories\WpUserRepository as UserRepository;
use App\WpUser as User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserMetaStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($userMeta)
    {
        $userRepo = new UserRepository();
        $user = User::find($this->userId);
        $userRepo->saveOrUpdateUserMeta($user, new ParameterBag($userMeta));
    }
}