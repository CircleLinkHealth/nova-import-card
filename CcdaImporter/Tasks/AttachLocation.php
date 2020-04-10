<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachLocation extends BaseCcdaImportTask
{
    protected function import()
    {
        if ($locationId = $this->ccda->location_id ?? null) {
            $this->patient->attachLocation($locationId);
        }
    }
}