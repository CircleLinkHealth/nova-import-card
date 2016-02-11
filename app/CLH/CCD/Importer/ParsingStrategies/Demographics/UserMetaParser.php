<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Demographics;


use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\DataTemplates\UserMetaTemplate;

class UserMetaParser implements ParsingStrategy
{
    private $template;

    public function __construct(UserMetaTemplate $template)
    {
        $this->template = $template;
    }

    public function parse($ccd, ValidationStrategy $validator = null)
    {
        $demographicsSection = $ccd->demographics;

        $this->template->first_name = ucwords(strtolower($demographicsSection->name->given[0]));
        $this->template->last_name = ucwords(strtolower($demographicsSection->name->family));
        $this->template->nickname = "";

        return $this->template;
    }
}