<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;

class AttachLocation extends BaseCcdaImportTask
{
    protected function import()
    {
        $locationId = $this->ccda->location_id ?? null;

        if ( ! $locationId && 1 === $this->patient->primaryPractice->locations->count()) {
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

        if ($this->patient->locations->isEmpty()) {
            $this->patient->attachLocation($locationId);
        }
    }
}
