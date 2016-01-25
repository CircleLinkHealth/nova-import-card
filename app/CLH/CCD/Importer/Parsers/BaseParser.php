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

    /**
     * The EHRs listed below do not fill out the end end date, or status for medications.
     * Medications that DO have a start date, but DO NOT HAVE an end date will be considered active.
     * We are setting the $importIfEndDateIsNull flag to point out those EHRs, and then we check if
     * the HAVE a start date but DO NOT HAVE and end date.
     */
    protected function importIfEndDateIsNullAndStartDateExists()
    {
        return in_array($this->ccd->document->legal_authenticator->representedOrganization->ids[0]->root, [
            '2.16.840.1.113883.3.929', // STI
        ]);
    }
}