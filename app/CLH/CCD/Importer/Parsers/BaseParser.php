<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\Contracts\CCD\Parser;
use App\CLH\Contracts\DataTemplate;
use App\ParsedCCD;

abstract class BaseParser implements Parser
{
    protected $blogId;
    protected $ccd;
    protected $meta;
    protected $parsedCcdObj;
    protected $userId;

    public function __construct ($blogId, ParsedCCD $ccd, DataTemplate $meta = null)
    {
        $this->blogId = $blogId;
        $this->ccd = json_decode($ccd->ccd);
        $this->meta = $meta;
        $this->parsedCcdObj = $ccd;
        $this->userId = $ccd->user_id;
    }
}