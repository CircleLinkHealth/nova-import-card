<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;

interface HasMedicalRecord
{
    /**
     * @return MedicalRecord
     */
    public function getMedicalRecord(): MedicalRecord;
}
