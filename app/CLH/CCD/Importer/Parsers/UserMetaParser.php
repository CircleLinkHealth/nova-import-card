<?php

namespace App\CLH\CCD\Importer\Parsers;

class UserMetaParser extends BaseParser
{
    public function parse()
    {
        $demographics = $this->ccd->demographics;

        $userMeta = $this->meta;

        $userMeta->first_name = $demographics->name->given[0];
        $userMeta->last_name = $demographics->name->family;
        $userMeta->nickname = "";

        return $userMeta;
    }
}