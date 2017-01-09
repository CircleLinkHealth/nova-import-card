<?php

namespace App\CLH\CCD\Importer\ParsingStrategies\Demographics;


use App\CLH\CCD\ItemLogger\DemographicsLog;
use App\CLH\Contracts\CCD\ParsingStrategy;
use App\CLH\Contracts\CCD\ValidationStrategy;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\Models\CCD\Ccda;

class UserMeta implements ParsingStrategy
{
    private $template;

    public function __construct(UserMetaTemplate $template)
    {
        $this->template = $template;
    }

    public function parse(Ccda $ccd, ValidationStrategy $validator = null)
    {
        $demographicsSection = DemographicsLog::whereCcdaId($ccd->id)->first();

        $this->template->first_name = ucwords(strtolower($demographicsSection->first_name));
        $this->template->last_name = ucwords(strtolower($demographicsSection->last_name));
        $this->template->nickname = "";

        return $this->template;
    }
}