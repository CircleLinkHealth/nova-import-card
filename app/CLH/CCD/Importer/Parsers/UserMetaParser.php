<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\Contracts\CCD\Parser;
use App\CLH\Repositories\UserRepository;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserMetaParser extends BaseParser implements Parser
{
    public function parse()
    {
        $demographics = $this->ccd->demographics;

        $userMeta = $this->meta;

        $userMeta->first_name = ucwords(strtolower($demographics->name->given[0]));
        $userMeta->last_name = ucwords(strtolower($demographics->name->family));
        $userMeta->nickname = "";

        return $userMeta;
    }

    public function save($data)
    {
        $userRepo = new UserRepository();
        $user = User::find($this->userId);
        $userRepo->saveOrUpdateUserMeta($user, new ParameterBag($data));
    }
}