<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\Contracts\CCD\Parser;
use App\CLH\Repositories\WpUserRepository;
use App\WpUser;
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
        $userRepo = new WpUserRepository();
        $wpUser = WpUser::find($this->userId);
        $userRepo->saveOrUpdateUserMeta($wpUser, new ParameterBag($data));
    }
}