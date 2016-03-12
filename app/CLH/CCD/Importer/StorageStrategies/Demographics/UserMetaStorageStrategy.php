<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Demographics;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\CLH\Repositories\UserRepository;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserMetaStorageStrategy extends BaseStorageStrategy implements StorageStrategy
{
    public function import($userMeta)
    {
        $userRepo = new UserRepository();
        $user = User::find( $this->user->ID );
        $userRepo->saveOrUpdateUserMeta( $user, new ParameterBag( $userMeta ) );
    }
}