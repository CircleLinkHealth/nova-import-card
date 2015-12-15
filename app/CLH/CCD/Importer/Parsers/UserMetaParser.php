<?php

namespace App\CLH\CCD\Importer\Parsers;

class UserMetaParser extends BaseParser
{
    public function parse()
    {
        $demographics = $this->ccd->demographics;

        $userMeta = $this->meta;

        $userMeta->first_name = ucfirst(strtolower($demographics->name->given[0]));
        $userMeta->last_name = ucfirst(strtolower($demographics->name->family));
        $userMeta->nickname = "";

        return $userMeta;
    }
}