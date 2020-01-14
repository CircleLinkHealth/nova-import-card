<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\CCD\Importer\StorageStrategies\Allergies;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies\BaseStorageStrategy;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategy;
use CircleLinkHealth\CarePlanModels\Entities\CpmInstruction;
use CircleLinkHealth\CarePlanModels\Entities\CpmMisc;

class AllergiesList extends BaseStorageStrategy implements StorageStrategy
{
    public function import($allergiesList)
    {
        if (empty($allergiesList)) {
            return;
        }

        if (empty($this->blogId) or empty($this->user)) {
            throw new \Exception('UserID and BlogID are required.');
        }

        $instruction = CpmInstruction::create([
            'name' => $allergiesList,
        ]);

        $misc = CpmMisc::whereName(CpmMisc::ALLERGIES)
            ->first();

        $this->user->cpmMiscs()->attach($misc->id, [
            'cpm_instruction_id' => $instruction->id,
        ]);
    }
}
