<?php

namespace App\CLH\CCD\CarePlanGenerator\Parsers\Demographics;


use App\CLH\Contracts\CCD\ParserWithoutValidation;
use App\CLH\Contracts\CCD\Validator;
use App\CLH\DataTemplates\UserMetaTemplate;

class UserMetaParser implements ParserWithoutValidation
{
    private $template;

    public function __construct(UserMetaTemplate $template)
    {
        $this->template = $template;
    }

    public function parse($demographicsSection)
    {
        $this->template->first_name = ucwords(strtolower($demographicsSection->name->given[0]));
        $this->template->last_name = ucwords(strtolower($demographicsSection->name->family));
        $this->template->nickname = "";

        return $this->template;
    }
}