<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;

interface HasMedicalRecord
{
    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordForEligibilityCheck
     */
    public function getMedicalRecord(): MedicalRecord;
}
