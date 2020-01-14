<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies\Medications;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BaseStorageStrategy;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategy;
use CircleLinkHealth\CarePlanModels\Entities\CpmInstruction;
use CircleLinkHealth\CarePlanModels\Entities\CpmMisc;

class MedicationsList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($medsList)
    {
        if (empty($medsList)) {
            return;
        }

        $instruction = CpmInstruction::create([
            'name' => $medsList,
        ]);

        $misc = CpmMisc::whereName(CpmMisc::MEDICATION_LIST)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id,
        ]);
    }
}
