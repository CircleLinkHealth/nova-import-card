<?php

namespace App\CLH\CCD\Importer\Parsers;

use App\CLH\CCD\Importer\Parsers\Helpers\MedicationsParserHelpers;

class MedicationsParser extends BaseParser
{
    /**
     * THIS IS GROSS. NEEDS REFACTORING.
     * Updates Medications List
     */
    public function parse()
    {
        (new MedicationsParserHelpers())->importFromCCD($this->userId, $this->blogId, $this->ccd);
    }
}