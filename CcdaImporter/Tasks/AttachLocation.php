<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachLocation extends BaseCcdaImportTask
{
    protected function import()
    {
        $locationId = $this->ccda->location_id ?? null;
        
        if ( ! $locationId && $this->patient->primaryPractice->locations->count() === 1) {
            $locationId = $this->patient->primaryPractice->locations->first()->id;
        }
        
        if ( ! $locationId) {
            return;
        }
        
        $this->patient->setPreferredContactLocation($locationId);
        
        if ($timezone = optional($this->patient->primaryPractice->locations->first())->timezone) {
            $this->patient->timezone = $timezone;
            $this->patient->save();
        }
    }
}