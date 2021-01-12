<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BloodPressure;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\Weight;

class ImportVitals extends BaseCcdaImportTask
{
    protected function import()
    {
        $decodedCcda = $this->ccda->bluebuttonJson();

        //Weight
        $weightParseAndStore = new Weight($this->patient->program_id, $this->patient);
        $weight              = $weightParseAndStore->parse($decodedCcda);
        if ( ! empty($weight)) {
            $weightParseAndStore->import($weight);
        }

        //Blood Pressure
        $bloodPressureParseAndStore = new BloodPressure($this->patient->program_id, $this->patient);
        $bloodPressure              = $bloodPressureParseAndStore->parse($decodedCcda);
        if ( ! empty($bloodPressure)) {
            $bloodPressureParseAndStore->import($bloodPressure);
        }
    }
}
