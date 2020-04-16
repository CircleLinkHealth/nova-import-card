<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachLocation extends BaseCcdaImportTask
{
    protected function import()
    {
        if ($locationId = $this->ccda->location_id ?? null) {
            $this->patient->attachLocation($locationId);
            
            $timezone = Location::whereNotNull('timezone')->find($locationId)->value('timezone');
            
            if ($timezone) {
                $this->patient->timezone = $timezone;
                $this->patient->save();
            }
        }
    }
}