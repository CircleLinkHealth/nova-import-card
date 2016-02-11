<?php

namespace App\CLH\CCD\CarePlanGenerator\Importers\Demographics;


use App\CLH\CCD\CarePlanGenerator\Importers\BaseImporter;
use App\CLH\Contracts\CCD\Importer;
use App\CLH\Repositories\WpUserRepository as UserRepository;
use App\WpUser as User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserMetaImporter extends BaseImporter implements Importer
{
    public function import($userMeta)
    {
        $userRepo = new UserRepository();
        $user = User::find($this->userId);
        $userRepo->saveOrUpdateUserMeta($user, new ParameterBag($userMeta->getArray()));
    }
}